<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use ZipArchive;

class CertificateGeneratorService
{
    private $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Core generator that creates the individual certificate files and adds them to a ZIP archive.
     */
    public function generateToZip(
        array $names,
        string $templatePath,
        array $config,
        string $zipPath,
        ?string $progressId = null
    ): bool {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $baseImage = $this->manager->decodePath($templatePath);
        $originalWidth = $baseImage->width();
        $originalHeight = $baseImage->height();

        $x = (int) (($config['percent_x'] / 100) * $originalWidth);
        $y = (int) (($config['percent_y'] / 100) * $originalHeight);

        $fontPath = $this->resolveFontPathByName($config['font_family']);
        $baseFontSize = max(12, (int) round($originalWidth * 0.04));
        $requestedSize = (int) round($baseFontSize * ($config['font_scale'] / 100));

        $outputWidth = max(100, (int) round($originalWidth * ($config['resolution_scale'] / 100)));
        $outputHeight = max(100, (int) round($originalHeight * ($config['resolution_scale'] / 100)));

        // Paper configuration setup
        $usePaper = $config['use_paper'] ?? false;
        $paperSize = $config['paper_size'] ?? 'A4';
        $format = $config['format'] ?? 'png';

        $resScale = $config['resolution_scale'] / 100;
        $paperPixelsW = 1240 * $resScale;
        $paperPixelsH = 1754 * $resScale;
        if ($paperSize === 'F4') {
            $paperPixelsH = 1950 * $resScale;
        }

        $paperOrientation = $config['paper_orientation'] ?? 'auto';
        if ($paperOrientation === 'auto') {
            $paperOrientation = $originalWidth > $originalHeight ? 'L' : 'P';
        }
        if ($paperOrientation === 'L') {
            $tmp = $paperPixelsW;
            $paperPixelsW = $paperPixelsH;
            $paperPixelsH = $tmp;
        }

        $totalNames = count($names);

        foreach ($names as $index => $name) {
            $image = clone $baseImage;

            $fontSize = $this->fitFontSize(
                $requestedSize,
                $name,
                $fontPath,
                $x,
                $y,
                $originalWidth,
                $originalHeight
            );

            $image->text($name, $x, $y, function ($font) use ($fontPath, $fontSize) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#000000');
                $font->align('center', 'center');
            });

            if ($usePaper && in_array($paperSize, ['A4', 'F4']) && in_array($format, ['png', 'jpg'])) {
                $drawW = ($config['img_w'] / 100) * $paperPixelsW;
                $drawH = ($config['img_h'] / 100) * $paperPixelsH;
                $image = $image->resize(width: (int)$drawW, height: (int)$drawH);
            } else {
                if ($config['resolution_scale'] !== 100.0) {
                    $image = $image->scale(width: $outputWidth);
                }
            }

            $safeName = preg_replace('/[^A-Za-z0-9\-\s]/', '', $name);
            $safeName = preg_replace('/\s+/', ' ', trim($safeName));
            if ($safeName === '') {
                $safeName = 'sertifikat_' . bin2hex(random_bytes(4));
            }

            if ($format === 'pdf') {
                $this->exportPdf($zip, $image, $safeName, $outputWidth, $outputHeight, $config, $paperPixelsW, $paperPixelsH);
            } else {
                $this->exportImage($zip, $image, $safeName, $format, $config, $paperPixelsW, $paperPixelsH);
            }

            if ($progressId) {
                $percent = (int) ((($index + 1) / $totalNames) * 100);
                cache()->put("progress_{$progressId}", $percent, 120);
            }

            unset($image);
        }

        $zip->close();
        unset($baseImage);

        return true;
    }

    private function exportPdf($zip, $image, $safeName, $outputWidth, $outputHeight, $config, $paperPixelsW, $paperPixelsH)
    {
        $encodedImage = $image->encodeUsingFileExtension('jpg');
        $tempImgFile = tempnam(sys_get_temp_dir(), 'cert_img');
        file_put_contents($tempImgFile, (string) $encodedImage);

        if ($config['use_paper'] && in_array($config['paper_size'], ['A4', 'F4'])) {
            $w = 210;
            $h = ($config['paper_size'] === 'F4') ? 330 : 297;
            
            $orientation = $config['paper_orientation'] === 'auto' ? ($outputWidth > $outputHeight ? 'L' : 'P') : $config['paper_orientation'];
            $pdf = new \FPDF($orientation, 'mm', [$w, $h]);
            $pdf->AddPage();

            if ($config['fit_mode'] === 'smaller') {
                $drawW = ($config['img_w'] / 100) * $pdf->w;
                $drawH = ($config['img_h'] / 100) * $pdf->h;
                $posX  = ($config['img_x'] / 100) * $pdf->w;
                $posY  = ($config['img_y'] / 100) * $pdf->h;
                $pdf->Image($tempImgFile, $posX, $posY, $drawW, $drawH, 'jpg');
            } else {
                $pdf->Image($tempImgFile, 0, 0, $pdf->w, $pdf->h, 'jpg');
            }
        } else {
            $orientation = $outputWidth > $outputHeight ? 'L' : 'P';
            $pdf = new \FPDF($orientation, 'pt', [$outputWidth, $outputHeight]);
            $pdf->AddPage();
            $pdf->Image($tempImgFile, 0, 0, $outputWidth, $outputHeight, 'jpg');
        }

        $pdfContent = $pdf->Output('S');
        $zip->addFromString($safeName . '.pdf', $pdfContent);
        unlink($tempImgFile);
    }

    private function exportImage($zip, $image, $safeName, $format, $config, $paperPixelsW, $paperPixelsH)
    {
        if ($config['use_paper'] && in_array($config['paper_size'], ['A4', 'F4'])) {
            $posX = ($config['img_x'] / 100) * $paperPixelsW;
            $posY = ($config['img_y'] / 100) * $paperPixelsH;

            $paperCanvas = $this->manager->createImage(width: (int)$paperPixelsW, height: (int)$paperPixelsH);
            $paperCanvas->fill('#ffffff');
            $paperCanvas->insert($image, (int)$posX, (int)$posY, \Intervention\Image\Alignment::TOP_LEFT);

            $encodedImage = $paperCanvas->encodeUsingFileExtension($format);
            $zip->addFromString($safeName . '.' . $format, (string) $encodedImage);
            unset($paperCanvas);
        } else {
            $encodedImage = $image->encodeUsingFileExtension($format);
            $zip->addFromString($safeName . '.' . $format, (string) $encodedImage);
        }
    }

    private function resolveFontPathByName(string $fontName): string
    {
        $map = [
            'roboto' => 'Roboto-Bold.ttf',
            'montserrat' => 'Montserrat-Bold.ttf',
            'playfair' => 'PlayfairDisplay-Bold.ttf',
            'alex brush' => 'AlexBrush-Regular.ttf',
            'cinzel' => 'Cinzel-Bold.ttf',
            'comic sans' => 'ComicSans.ttf',
            'times new roman' => 'TimesNewRoman.ttf',
            'arial' => 'Arial.ttf',
        ];

        $key = strtolower(trim(explode('-', $fontName)[0])); // normalize
        $fileName = $map[$key] ?? 'Roboto-Bold.ttf';

        $path = public_path("fonts/{$fileName}");
        if (file_exists($path)) return $path;

        return public_path('fonts/Roboto-Bold.ttf');
    }

    private function fitFontSize($requestedSize, $text, $fontPath, $anchorX, $anchorY, $imageWidth, $imageHeight): int
    {
        $maxSize  = max(8, $requestedSize);
        $minSize  = max(8, (int) floor($requestedSize * 0.25));

        $low = $minSize;
        $high = $maxSize;
        $optimalSize = $minSize;

        while ($low <= $high) {
            $mid = (int) (($low + $high) / 2);
            $bbox = imagettfbbox($mid, 0, $fontPath, $text);
            if ($bbox === false) {
                $high = $mid - 1;
                continue;
            }

            $textWidth  = abs($bbox[4] - $bbox[0]);
            $textHeight = abs($bbox[5] - $bbox[1]);
            $left   = $anchorX - ($textWidth / 2);
            $right  = $anchorX + ($textWidth / 2);
            $top    = $anchorY - ($textHeight / 2);
            $bottom = $anchorY + ($textHeight / 2);

            if ($left >= 0 && $right <= $imageWidth && $top >= 0 && $bottom <= $imageHeight) {
                $optimalSize = $mid;
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }
        return $optimalSize;
    }
}
