<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
class CertificateController extends Controller
{
    public function index()
    {
        return view('certificate.index');
    }
    public function generate(Request $request)
    {
        $request->validate([
            'template' => 'required|image|mimes:jpeg,png,jpg',
// Halaman 1 dari 3
            'csv_file' => 'required|file|mimes:csv,txt',
            'x_pos' => 'required|numeric|between:0,100',
            'y_pos' => 'required|numeric|between:0,100',
        ]);
        $templatePath = $request->file('template')->path();
        $csvPath = $request->file('csv_file')->path();
        $manager = new ImageManager(new Driver());
        // Membaca ukuran asli dari template
        $originalImage = $manager->read($templatePath);
        $originalWidth = $originalImage->width();
        $originalHeight = $originalImage->height();
        // Konversi koordinat % ke pixel absolut
        $pixelX = ($request->x_pos / 100) * $originalWidth;
        $pixelY = ($request->y_pos / 100) * $originalHeight;
        $zip = new ZipArchive;
        $zipFileName = 'certificates_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            $reader = ReaderFactory::createFromFile($csvPath);
            $reader->open($csvPath);
            $index = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($index === 0) { // Lewati header CSV jika ada
                        $index++;
                        continue;
                    }
                    $nama = $row->getCellAtIndex(0)->getValue();
                    if (empty($nama)) continue;
                    // Buat instance gambar baru dari template asal untuk setiap nama
                    $image = $manager->read($templatePath);
                    // Tempelkan tulisan nama ke gambar
                    $image->text($nama, $pixelX, $pixelY, function($font) {
                        $font->file(public_path('fonts/Roboto-Bold.ttf'));
                        $font->size(55); // Anda bisa menyesuaikan ini atau menambahkannya di form input
                        $font->color('#1a202c');
                        $font->align('center');
                        $font->valign('middle');
                    });
                    // Simpan sementara di storage local
                    $tempFileName = 'cert_' . Str::slug($nama) . '_' . uniqid() . '.png';
                    $tempPath = storage_path('app/public/temp/' . $tempFileName);
// Halaman 2 dari 3
                    if (!file_exists(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0755, true);
                    }
                    $image->toPng()->save($tempPath);
                    // Masukkan ke ZIP
                    $zip->addFile($tempPath, 'Sertifikat_' . Str::slug($nama) . '.png');
                    $tempFiles[] = $tempPath;
                }
            }
            $reader->close();
            $zip->close();
            // Hapus file temporary agar tidak membebani storage disk
            foreach ($tempFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
        return Storage::disk('public')->download($zipFileName)->deleteFileAfterSend(true);
    }
}