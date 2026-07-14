<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'template'  => 'required|image|mimes:png,jpg,jpeg,webp,gif,bmp',
            'csv_file'  => 'required|file|mimes:csv,txt',
            'x_pos'     => 'required|numeric',
            'y_pos'     => 'required|numeric',
            'format'    => 'required|in:png,jpg,pdf',
        ]);

        $template = $request->file('template');
        $csvFile  = $request->file('csv_file');

        $percentX = (float) $request->input('x_pos');
        $percentY = (float) $request->input('y_pos');
        $format   = $request->input('format', 'png');

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
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
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

                $names[] = $cellValue;
            }
            fclose($handle);
        }

        if (empty($names)) {
            return back()->withErrors(['csv_file' => 'File CSV kosong atau tidak ada nama yang bisa dibaca di kolom A.']);
        }

        // ─── Setup Image Manager ─────────────────────────────────────────────
        $manager = ImageManager::usingDriver(Driver::class);

        // Ambil dimensi gambar asli untuk konversi koordinat persen → pixel (Decode sekali saja)
        $baseImage      = $manager->decode($template->getRealPath());
        $originalWidth  = $baseImage->width();
        $originalHeight = $baseImage->height();

        $x = (int) (($percentX / 100) * $originalWidth);
        $y = (int) (($percentY / 100) * $originalHeight);

        $fontPath = public_path('fonts/Roboto-Bold.ttf');

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

            $image->text($name, $x, $y, function ($font) use ($fontPath) {
                $font->file(file_exists($fontPath) ? $fontPath : 'C:\\Windows\\Fonts\\arial.ttf');
                $font->size(64);
                $font->color('#000000');
                $font->align('center', 'center');
            });

            // Nama file di dalam ZIP = nama dari CSV (spasi → underscore, karakter aneh dihapus)
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '_', $name);
            $safeName = preg_replace('/_+/', '_', trim($safeName, '_')); // rapikan underscore ganda

            // Fallback jika nama kosong (misal berisi huruf non-latin seperti Arab/Mandarin, atau emoji)
            if ($safeName === '') {
                $safeName = 'sertifikat_' . bin2hex(random_bytes(4));
            }

            if ($format === 'pdf') {
                // Simpan gambar sementara ke disk untuk dibaca Dompdf (jauh lebih cepat daripada base64)
                $tempImagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'temp_cert_' . uniqid() . '.png';
                $image->save($tempImagePath);

                $pdf = Pdf::loadHTML("
                    <html>
                    <body style='margin:0;padding:0;text-align:center;background:#fff;'>
                        <img src='{$tempImagePath}' style='width:100%;height:auto;display:block;' />
                    </body>
                    </html>
                ")->setPaper('A4', 'landscape');

                $zip->addFromString($safeName . '.pdf', $pdf->output());

                // Hapus file temp setelah selesai
                if (file_exists($tempImagePath)) {
                    unlink($tempImagePath);
                }
            } elseif ($format === 'jpg') {
                $encodedImage = $image->encodeUsingFileExtension('jpg');
                $zip->addFromString($safeName . '.jpg', (string) $encodedImage);
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
            unset($image, $encodedImage, $pdf, $tempImagePath);
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
}