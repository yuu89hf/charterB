<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia; // Jika pakai Inertia, tapi kita asumsikan Blade sesuai Breeze standar

class CertificateController extends Controller
{
    public function index()
    {
        return view('certificate.index');
    }

    public function generate(Request $request)
    {
        // Nanti kita akan taruh logic Intervention Image di sini
        // Untuk sekarang, kita fokus ke UI dulu!
    }
}