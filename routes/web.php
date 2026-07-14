<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CertificateController; // Nama controller diubah!
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Hmph, perhatikan di sini! Kita satukan dashboard dan fitur piagam 
// ke dalam satu grup middleware yang mewajibkan login & verifikasi email.
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', function() {
        return view('dashboard');
    })->name('dashboard');

    // Route untuk menampilkan halaman upload piagam
    Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate.index');

    // Route untuk memproses upload dan generate piagam
    Route::post('/certificate/generate', [CertificateController::class, 'generate'])->name('certificate.generate');
});

// Route untuk profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
