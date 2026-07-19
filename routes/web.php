<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
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
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');

    // Route untuk memproses upload dan generate piagam
    Route::post('/workspace/generate', [WorkspaceController::class, 'generate'])->name('workspace.generate');

    // Route untuk melihat progress pembuatan sertifikat
    Route::get('/workspace/progress/{progressId}', [WorkspaceController::class, 'progress'])->name('workspace.progress');

    // Route untuk mengunduh berkas ZIP hasil generate
    Route::get('/workspace/download/{file}', [WorkspaceController::class, 'download'])->name('workspace.download');
});

// Route untuk profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
