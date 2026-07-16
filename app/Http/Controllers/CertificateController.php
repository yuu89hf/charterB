<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Cookie;
use ZipArchive;

class CertificateController extends Controller
{
    public function index()
    {
        return view('certificate.index');
    }

    public function generate(Request $request)
    {
        // Tidak ada timeout — penting untuk CSV dengan banyak baris (100+)
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $request->validate([
            'template'          => 'required|image|mimes:png,jpg,jpeg,webp,gif,bmp',
            'csv_file'          => 'required|file|mimes:csv,txt',
            'x_pos'             => 'required|numeric',
            'y_pos'             => 'required|numeric',
            'format'            => 'required|in:png,jpg,pdf',
            'font_scale'        => 'required|numeric|min:25|max:300',
            'resolution_scale'  => 'required|numeric|min:25|max:300',
            'font_family'       => 'nullable|string',
            'row_start'         => 'nullable|integer|min:1',
            'row_end'           => 'nullable|integer|min:1',
            'row_exclude'       => 'nullable|string',
            'use_paper'         => 'nullable|string|in:y,n',
            'paper_size'        => 'nullable|string|in:A4,F4',
            'paper_orientation' => 'nullable|string|in:auto,L,P',
            'fit_mode'          => 'nullable|string|in:full,smaller',
            'margin'            => 'nullable|numeric|min:0|max:5',
            'img_x'             => 'nullable|numeric',
            'img_y'             => 'nullable|numeric',
            'img_w'             => 'nullable|numeric',
            'img_h'             => 'nullable|numeric',
        ]);

        $template = $request->file('template');
        $csvFile  = $request->file('csv_file');

        $percentX   = (float) $request->input('x_pos');
        $percentY   = (float) $request->input('y_pos');
        $format           = $request->input('format', 'png');
        $fontScale        = (float) $request->input('font_scale', 100);
        $resolutionScale  = (float) $request->input('resolution_scale', 100);
        $usePaper         = $request->input('use_paper') === 'y';
        $paperSize        = $request->input('paper_size', 'A4');
        $paperOrientation = $request->input('paper_orientation', 'auto');
        $fitMode          = $request->input('fit_mode', 'full');
        $marginCm         = (float) $request->input('margin', 1.0);
        $imgX             = (float) $request->input('img_x', 0);
        $imgY             = (float) $request->input('img_y', 0);
        $imgW             = (float) $request->input('img_w', 100);
        $imgH             = (float) $request->input('img_h', 100);

        $rowStart = $request->input('row_start') ? (int) $request->input('row_start') : null;
        $rowEnd   = $request->input('row_end') ? (int) $request->input('row_end') : null;
        
        $excludeInput = $request->input('row_exclude', '');
        $excludeRows = [];
        if (!empty(trim($excludeInput))) {
            $excludeRows = preg_split('/[^0-9]+/', trim($excludeInput));
            $excludeRows = array_map('intval', array_filter($excludeRows, 'strlen'));
        }

        // ─── Baca CSV (Kolom A) ──────────────────────────────────────────────
        // Auto-skip baris pertama jika isinya "nama", "name", atau "no" (header)
        $names = [];
        if (($handle = fopen($csvFile->getRealPath(), 'r')) !== false) {
            // Deteksi otomatis pembatas/separator (koma ',' atau titik koma ';')
            // Hal ini penting karena Excel di Indonesia sering menyimpan CSV dengan separator ';'
            $delimiter = ',';
            $firstLine = fgets($handle);
            if ($firstLine !== false) {
                $commaCount = substr_count($firstLine, ',');
                $semicolonCount = substr_count($firstLine, ';');
                if ($semicolonCount > $commaCount) {
                    $delimiter = ';';
                }
            }
            rewind($handle);

            $firstRow = true;
            $rowNum = 0;
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNum++;
                if (!isset($data[0]) || trim($data[0]) === '') {
                    continue;
                }

                $cellValue = trim($data[0]);

                // Skip baris pertama jika berisi kata "nama", "name", atau "no"
                if ($firstRow) {
                    $firstRow = false;
                    $lower = strtolower($cellValue);
                    if (in_array($lower, ['nama', 'name', 'no', 'no.', 'nomor', 'number'])) {
                        continue; // lewati header
                    }
                }

                // Filter berdasarkan baris awal (start row)
                if ($rowStart !== null && $rowNum < $rowStart) {
                    continue;
                }

                // Filter berdasarkan baris akhir (end row)
                if ($rowEnd !== null && $rowNum > $rowEnd) {
                    continue;
                }

                // Filter baris yang dikecualikan (exclude)
                if (in_array($rowNum, $excludeRows)) {
                    continue;
                }

                $names[] = $cellValue;
            }
            fclose($handle);
        }

        if (empty($names)) {
            return back()->withErrors(['csv_file' => 'No names found in CSV matching the specified row range/exclusions.']);
        }

        // ─── Setup Image Manager ─────────────────────────────────────────────
        $manager = ImageManager::usingDriver(Driver::class);

        // Ambil dimensi gambar asli untuk konversi koordinat persen → pixel (Decode sekali saja)
        $baseImage      = $manager->decode($template->getRealPath());
        $originalWidth  = $baseImage->width();
        $originalHeight = $baseImage->height();

        $x = (int) (($percentX / 100) * $originalWidth);
        $y = (int) (($percentY / 100) * $originalHeight);

        $defaultFontFamily = $request->input('font_family', 'Roboto-Bold');
        $fontPath          = $this->resolveFontPathByName($defaultFontFamily);
        $baseFontSize      = $this->calculateBaseFontSize($originalWidth);
        $requestedSize     = (int) round($baseFontSize * ($fontScale / 100));
        $outputWidth       = max(100, (int) round($originalWidth * ($resolutionScale / 100)));
        $outputHeight      = max(100, (int) round($originalHeight * ($resolutionScale / 100)));

        // Calculate paper page dimensions in pixels for JPG/PNG export
        $resScale = $resolutionScale / 100;
        $paperPixelsW = 1240 * $resScale;
        $paperPixelsH = 1754 * $resScale;
        if ($paperSize === 'F4') {
            $paperPixelsH = 1950 * $resScale;
        }
        $paperDetectOrientation = $paperOrientation;
        if ($paperDetectOrientation === 'auto') {
            $paperDetectOrientation = $originalWidth > $originalHeight ? 'L' : 'P';
        }
        if ($paperDetectOrientation === 'L') {
            $tmp = $paperPixelsW;
            $paperPixelsW = $paperPixelsH;
            $paperPixelsH = $tmp;
        }

        // ─── Buat file ZIP di folder temp sistem ─────────────────────────────
        $zipName = 'sertifikat_' . time() . '.zip';
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ZIP file (error code: ' . $opened . '). Make sure the temp folder is writable.'
            ], 500);
        }

        $progressId = $request->input('progress_id');
        $totalNames = count($names);
        if ($progressId) {
            cache()->put("progress_{$progressId}", 0, 120);
        }

        // ─── Generate sertifikat untuk setiap nama ───────────────────────────
        foreach ($names as $index => $name) {
            // Gunakan clone daripada decode berkali-kali (optimasi kecepatan utama!)
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
                // Calculate size in pixels relative to the white paper canvas
                $drawW = ($imgW / 100) * $paperPixelsW;
                $drawH = ($imgH / 100) * $paperPixelsH;
                $image = $image->resize(width: (int)$drawW, height: (int)$drawH);
            } else {
                if ($resolutionScale !== 100.0) {
                    $image = $image->scale(width: $outputWidth);
                }
            }

            // Nama file di dalam ZIP = nama dari CSV (karakter aneh dihapus, spasi dipertahankan)
            $safeName = preg_replace('/[^A-Za-z0-9\-\s]/', '', $name);
            $safeName = preg_replace('/\s+/', ' ', trim($safeName)); // rapikan spasi ganda

            // Fallback jika nama kosong (misal berisi huruf non-latin seperti Arab/Mandarin, atau emoji)
            if ($safeName === '') {
                $safeName = 'sertifikat_' . bin2hex(random_bytes(4));
            }

            if ($format === 'pdf') {
                $encodedImage = $image->encodeUsingFileExtension('jpg');
                
                // Write image to a temporary file
                $tempImgFile = tempnam(sys_get_temp_dir(), 'cert_img');
                file_put_contents($tempImgFile, (string) $encodedImage);

                if ($usePaper && in_array($paperSize, ['A4', 'F4'])) {
                    // Page dimensions in mm: A4 is 210x297, F4 is 210x330.
                    $w = 210;
                    $h = ($paperSize === 'F4') ? 330 : 297;

                    $orientation = $paperOrientation;
                    if ($orientation === 'auto') {
                        $orientation = $outputWidth > $outputHeight ? 'L' : 'P';
                    }

                    $pdf = new \FPDF($orientation, 'mm', [$w, $h]);
                    $pdf->AddPage();

                    $pageW = $pdf->w;
                    $pageH = $pdf->h;

                    if ($fitMode === 'smaller') {
                        $drawW = ($imgW / 100) * $pageW;
                        $drawH = ($imgH / 100) * $pageH;
                        $posX  = ($imgX / 100) * $pageW;
                        $posY  = ($imgY / 100) * $pageH;

                        $pdf->Image($tempImgFile, $posX, $posY, $drawW, $drawH, 'jpg');
                    } else {
                        $pdf->Image($tempImgFile, 0, 0, $pageW, $pageH, 'jpg');
                    }
                } else {
                    $orientation = $outputWidth > $outputHeight ? 'L' : 'P';
                    // Create PDF matching the image dimensions
                    $pdf = new \FPDF($orientation, 'pt', [$outputWidth, $outputHeight]);
                    $pdf->AddPage();
                    $pdf->Image($tempImgFile, 0, 0, $outputWidth, $outputHeight, 'jpg');
                }

                $pdfContent = $pdf->Output('S');

                $zip->addFromString($safeName . '.pdf', $pdfContent);
                unlink($tempImgFile);
            } else {
                if ($usePaper && in_array($paperSize, ['A4', 'F4'])) {
                    // Position coordinates in pixels relative to the white paper canvas
                    $posX = ($imgX / 100) * $paperPixelsW;
                    $posY = ($imgY / 100) * $paperPixelsH;

                    // Create white background paper canvas
                    $paperCanvas = $manager->create(width: (int)$paperPixelsW, height: (int)$paperPixelsH);
                    $paperCanvas->fill('#ffffff');

                    // Overlay template image on canvas
                    $paperCanvas->place(element: $image, position: 'top-left', offset_x: (int)$posX, offset_y: (int)$posY);

                    $encodedImage = $paperCanvas->encodeUsingFileExtension($format);
                    $zip->addFromString($safeName . '.' . $format, (string) $encodedImage);
                    unset($paperCanvas);
                } else {
                    $encodedImage = $image->encodeUsingFileExtension($format);
                    $zip->addFromString($safeName . '.' . $format, (string) $encodedImage);
                }
            }

            // Simpan progress ke Cache
            if ($progressId) {
                $percent = (int) ((($index + 1) / $totalNames) * 100);
                cache()->put("progress_{$progressId}", $percent, 120);
            }

            // Bebaskan memori tiap iterasi agar tidak OOM untuk 100+ file
            unset($image, $encodedImage);
        }

        $zip->close();
        unset($baseImage); // Bebaskan memori template asli

        if ($progressId) {
            cache()->put("progress_{$progressId}", 100, 120);
        }

        return response()->json([
            'success' => true,
            'download_url' => route('certificate.download', ['file' => $zipName])
        ]);
    }

    public function progress($progressId)
    {
        $progress = cache()->get("progress_{$progressId}", 0);
        return response()->json(['progress' => (int) $progress]);
    }

    public function download($file)
    {
        $file = basename($file);
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($zipPath)) {
            abort(404, 'File not found');
        }

        // Simpan cookie download_started agar JS tahu unduhan dimulai
        Cookie::queue('download_started', 'true', 1, '/', null, false, false);

        return response()->download($zipPath, $file)->deleteFileAfterSend(true);
    }

    private function resolveFontPathByName(string $fontName): string
    {
        $fontName = trim($fontName);
        $map = [
            'roboto' => 'Roboto-Bold.ttf',
            'roboto-bold' => 'Roboto-Bold.ttf',
            'montserrat' => 'Montserrat-Bold.ttf',
            'montserrat-bold' => 'Montserrat-Bold.ttf',
            'playfair' => 'PlayfairDisplay-Bold.ttf',
            'playfair display' => 'PlayfairDisplay-Bold.ttf',
            'playfairdisplay-bold' => 'PlayfairDisplay-Bold.ttf',
            'alex brush' => 'AlexBrush-Regular.ttf',
            'alexbrush' => 'AlexBrush-Regular.ttf',
            'alexbrush-regular' => 'AlexBrush-Regular.ttf',
            'cinzel' => 'Cinzel-Bold.ttf',
            'cinzel-bold' => 'Cinzel-Bold.ttf',
            'comic sans' => 'ComicSans.ttf',
            'comic sans ms' => 'ComicSans.ttf',
            'comic sans ms-bold' => 'ComicSans-Bold.ttf',
            'comicsans' => 'ComicSans.ttf',
            'times new roman' => 'TimesNewRoman.ttf',
            'timesnewroman' => 'TimesNewRoman.ttf',
            'times new roman-bold' => 'TimesNewRoman-Bold.ttf',
            'tnr' => 'TimesNewRoman.ttf',
            'arial' => 'Arial.ttf',
            'arial-bold' => 'Arial-Bold.ttf',
        ];

        $key = strtolower($fontName);
        $fileName = $map[$key] ?? 'Roboto-Bold.ttf';

        $path = public_path("fonts/{$fileName}");
        if (file_exists($path)) {
            return $path;
        }

        return $this->resolveFontPath();
    }

    private function resolveFontPath(): string
    {
        $customFont = public_path('fonts/Roboto-Bold.ttf');
        if (file_exists($customFont)) {
            return $customFont;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return 'C:\\Windows\\Fonts\\arial.ttf';
        }

        $linuxFonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];

        foreach ($linuxFonts as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $customFont;
    }

    private function calculateBaseFontSize(int $imageWidth): int
    {
        // 4% lebar gambar — skala proporsional dengan resolusi template
        return max(12, (int) round($imageWidth * 0.04));
    }

    private function fitFontSize(
        int $requestedSize,
        string $text,
        string $fontPath,
        int $anchorX,
        int $anchorY,
        int $imageWidth,
        int $imageHeight
    ): int {
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
                $optimalSize = $mid; // fits, record it and try larger size
                $low = $mid + 1;
            } else {
                $high = $mid - 1; // does not fit, try smaller size
            }
        }

        return $optimalSize;
    }
}
