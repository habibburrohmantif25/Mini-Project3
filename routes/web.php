<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
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

    // Modul Pelaporan Kejadian (SRS Phase 4)
    Route::get('/reports', [IncidentReportController::class, 'index'])->name('reports.index');

    // Khusus Pelapor (SRS 3.2)
    Route::middleware('role:pelapor')->group(function () {
        Route::get('/reports/create', [IncidentReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [IncidentReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{report}/edit', [IncidentReportController::class, 'edit'])->name('reports.edit');
        Route::put('/reports/{report}', [IncidentReportController::class, 'update'])->name('reports.update');
    });

    Route::get('/reports/{report}', [IncidentReportController::class, 'show'])->name('reports.show');

    // Route halaman rekap (Hanya Pemantau & Koordinator - SRS 3.2 & 6.6)
    Route::get('/rekap', function () {
        return 'Halaman Rekap Sederhana (Pemantau & Koordinator Only)';
    })->middleware('role:pemantau,koordinator')->name('rekap.index');
});
