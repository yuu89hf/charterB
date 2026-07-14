<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Barryvdh\DomPDF\Facade\Pdf;
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
            'template'  => 'required|image|mimes:png,jpg,jpeg',
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
            $firstRow = true;
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
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

        // Ambil dimensi gambar asli untuk konversi koordinat persen → pixel
        $tempImage      = $manager->decode($template->getRealPath());
        $originalWidth  = $tempImage->width();
        $originalHeight = $tempImage->height();
        unset($tempImage); // bebaskan memori

        $x = (int) (($percentX / 100) * $originalWidth);
        $y = (int) (($percentY / 100) * $originalHeight);

        $fontPath = public_path('fonts/Roboto-Bold.ttf');

        // ─── Buat file ZIP di folder temp sistem ─────────────────────────────
        // Gunakan sys_get_temp_dir() supaya pasti bisa ditulis di semua environment
        $zipName = 'sertifikat_' . time() . '.zip';
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            return back()->withErrors([
                'csv_file' => 'Gagal membuat file ZIP (kode error: ' . $opened . '). Pastikan folder temp bisa ditulis.',
            ]);
        }

        // ─── Generate sertifikat untuk setiap nama ───────────────────────────
        foreach ($names as $name) {
            $image = $manager->decode($template->getRealPath());

            $image->text($name, $x, $y, function ($font) use ($fontPath) {
                $font->file(file_exists($fontPath) ? $fontPath : 'C:\\Windows\\Fonts\\arial.ttf');
                $font->size(64);
                $font->color('#000000');
                $font->align('center', 'center');
            });

            // Nama file di dalam ZIP = nama dari CSV (spasi → underscore, karakter aneh dihapus)
            $safeName = preg_replace('/[^A-Za-z0-9\-]/', '_', $name);
            $safeName = preg_replace('/_+/', '_', trim($safeName, '_')); // rapikan underscore ganda

            if ($format === 'pdf') {
                $encoded = $image->encodeUsingFileExtension('png');
                $base64  = 'data:image/png;base64,' . base64_encode((string) $encoded);

                $pdf = Pdf::loadHTML("
                    <html>
                    <body style='margin:0;padding:0;text-align:center;background:#fff;'>
                        <img src='{$base64}' style='width:100%;height:auto;display:block;' />
                    </body>
                    </html>
                ")->setPaper('A4', 'landscape');

                $zip->addFromString($safeName . '.pdf', $pdf->output());
            } elseif ($format === 'jpg') {
                $encodedImage = $image->encodeUsingFileExtension('jpg');
                $zip->addFromString($safeName . '.jpg', (string) $encodedImage);
            } else {
                $encodedImage = $image->encodeUsingFileExtension('png');
                $zip->addFromString($safeName . '.png', (string) $encodedImage);
            }

            // Bebaskan memori tiap iterasi agar tidak OOM untuk 100+ file
            unset($image, $encodedImage, $encoded, $pdf, $base64);
        }

        $zip->close();

        // ─── Kirim ZIP ke browser lalu hapus ─────────────────────────────────
        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true)->withCookie(cookie('download_started', 'true', 1, '/', null, false, false));
    }
}