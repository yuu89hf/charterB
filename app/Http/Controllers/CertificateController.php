<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Cookie;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            'template'         => 'required|image|mimes:png,jpg,jpeg,webp,gif,bmp',
            'csv_file'         => 'required|file|mimes:csv,txt,xlsx,xls',
            'x_pos'            => 'required|numeric',
            'y_pos'            => 'required|numeric',
            'format'           => 'required|in:png,jpg,pdf',
            'font_scale'       => 'required|numeric|min:25|max:300',
            'resolution_scale' => 'required|numeric|min:25|max:300',
            'font_family'      => 'nullable|string',
            'row_start'        => 'nullable|integer|min:1',
            'row_end'          => 'nullable|integer|min:1',
            'row_exclude'      => 'nullable|string',
        ]);

        $template = $request->file('template');
        $csvFile  = $request->file('csv_file');

        $percentX   = (float) $request->input('x_pos');
        $percentY   = (float) $request->input('y_pos');
        $format           = $request->input('format', 'png');
        $fontScale        = (float) $request->input('font_scale', 100);
        $resolutionScale  = (float) $request->input('resolution_scale', 100);

        $rowStart = $request->input('row_start') ? (int) $request->input('row_start') : null;
        $rowEnd   = $request->input('row_end') ? (int) $request->input('row_end') : null;
        
        $excludeInput = $request->input('row_exclude', '');
        $excludeRows = [];
        if (!empty(trim($excludeInput))) {
            $excludeRows = preg_split('/[^0-9]+/', trim($excludeInput));
            $excludeRows = array_map('intval', array_filter($excludeRows, 'strlen'));
        }

        // ─── Baca CSV / Excel (Kolom A) ──────────────────────────────────────
        // Auto-skip baris pertama jika isinya "nama", "name", atau "no" (header)
        $names = [];
        try {
            $spreadsheet = IOFactory::load($csvFile->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            
            $firstRow = true;
            $rowNum = 0;
            
            for ($row = 1; $row <= $highestRow; $row++) {
                $rowNum++;
                $cellValue = $worksheet->getCell([1, $row])->getValue();
                
                // Jika cell bertipe RichText, konversi ke string biasa
                if ($cellValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $cellValue = $cellValue->getPlainText();
                }
                
                $cellValue = trim((string) $cellValue);
                if ($cellValue === '') {
                    continue;
                }

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
        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Failed to read spreadsheet file: ' . $e->getMessage()]);
        }

        if (empty($names)) {
            return back()->withErrors(['csv_file' => 'No names found in CSV/Excel matching the specified row range/exclusions.']);
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

        // ─── Buat file ZIP di folder temp sistem ─────────────────────────────
        $zipName = 'sertifikat_' . time() . '.zip';
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat file ZIP (kode error: ' . $opened . '). Pastikan folder temp bisa ditulis.'
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

            if ($resolutionScale !== 100.0) {
                $image = $image->scale(width: $outputWidth);
            }

            // Nama file di dalam ZIP = nama dari CSV (karakter aneh dihapus, spasi dipertahankan)
            $safeName = preg_replace('/[^A-Za-z0-9\-\s]/', '', $name);
            $safeName = preg_replace('/\s+/', ' ', trim($safeName)); // rapikan spasi ganda

            // Fallback jika nama kosong (misal berisi huruf non-latin seperti Arab/Mandarin, atau emoji)
            if ($safeName === '') {
                $safeName = 'sertifikat_' . bin2hex(random_bytes(4));
            }

            if ($format === 'jpg') {
                $encodedImage = $image->encodeUsingFileExtension('jpg');
                $zip->addFromString($safeName . '.jpg', (string) $encodedImage);
            } elseif ($format === 'pdf') {
                // Konversi px ke pt (1 px = 0.75 pt) agar dimensi PDF pas dengan rasio gambar
                $widthPt = $outputWidth * 0.75;
                $heightPt = $outputHeight * 0.75;
                $orientation = $widthPt > $heightPt ? 'L' : 'P';
                
                $pdf = new \FPDF($orientation, 'pt', [$widthPt, $heightPt]);
                $pdf->AddPage();
                
                // Simpan image ke file temp untuk diproses FPDF
                $tempImg = tempnam(sys_get_temp_dir(), 'cert_');
                file_put_contents($tempImg, (string) $image->encodeUsingFileExtension('jpg'));
                
                $pdf->Image($tempImg, 0, 0, $widthPt, $heightPt, 'JPG');
                unlink($tempImg);
                
                $zip->addFromString($safeName . '.pdf', $pdf->Output('S'));
            } else {
                $encodedImage = $image->encodeUsingFileExtension('png');
                $zip->addFromString($safeName . '.png', (string) $encodedImage);
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
        $fontSize = max(8, $requestedSize);
        $minSize  = max(8, (int) floor($requestedSize * 0.25));

        while ($fontSize >= $minSize) {
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox === false) {
                return $fontSize;
            }

            $textWidth  = abs($bbox[4] - $bbox[0]);
            $textHeight = abs($bbox[5] - $bbox[1]);

            $left   = $anchorX - ($textWidth / 2);
            $right  = $anchorX + ($textWidth / 2);
            $top    = $anchorY - ($textHeight / 2);
            $bottom = $anchorY + ($textHeight / 2);

            if ($left >= 0 && $right <= $imageWidth && $top >= 0 && $bottom <= $imageHeight) {
                return $fontSize;
            }

            $fontSize--;
        }

        return $minSize;
    }
}
