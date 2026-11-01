<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Halaman Landing Publik (SRS 3.2)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Autentikasi Pengguna (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Area Terproteksi (Wajib Login - SRS 6.1)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route laporan (Placeholder sementara untuk pengujian otorisasi Phase 3)
    Route::get('/reports', function () {
        return redirect()->route('dashboard');
    })->name('reports.index');

    // Route membuat laporan (Hanya peran Pelapor - SRS 3.2)
    Route::get('/reports/create', function () {
        return 'Form Pelaporan Kejadian (Pelapor Only)';
    })->middleware('role:pelapor')->name('reports.create');

    // Route halaman rekap (Hanya Pemantau & Koordinator - SRS 3.2 & 6.6)
    Route::get('/rekap', function () {
        return 'Halaman Rekap Sederhana (Pemantau & Koordinator Only)';
    })->middleware('role:pemantau,koordinator')->name('rekap.index');
});
