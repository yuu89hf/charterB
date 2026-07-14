<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('halaman editor sertifikat hanya bisa diakses oleh user terverifikasi', function () {
    $userUnverified = User::factory()->create([
        'email_verified_at' => null
    ]);

    $userVerified = User::factory()->create([
        'email_verified_at' => now()
    ]);

    $this->actingAs($userUnverified)
        ->get(route('certificate.index'))
        ->assertRedirect(route('verification.notice'));

    $this->actingAs($userVerified)
        ->get(route('certificate.index'))
        ->assertStatus(200);
});

test('user dapat mengunggah template dan csv untuk menghasilkan zip sertifikat', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'email_verified_at' => now()
    ]);

    // Buat mock gambar template sertifikat (.png)
    $template = UploadedFile::fake()->image('template_sertifikat.png', 1920, 1080);
    
    // Buat mock file CSV berisi daftar nama
    $csvContent = "Nama\nWeixi\nLingyin\nJane Doe";
    $csvFile = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

    $response = $this->actingAs($user)
        ->postJson(route('certificate.generate'), [
            'template' => $template,
            'csv_file' => $csvFile,
            'x_pos' => 50,
            'y_pos' => 50,
            'format' => 'png',
            'font_scale' => 100,
            'resolution_scale' => 100,
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    $response->assertJsonStructure(['download_url']);
});