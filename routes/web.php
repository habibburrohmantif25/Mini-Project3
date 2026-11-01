<?php

use App\Http\Controllers\Api\SchoolDistributionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\RekapController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/api/schools/{school}/distributions', [SchoolDistributionController::class, 'index'])->name('api.schools.distributions');

    Route::get('/reports', [IncidentReportController::class, 'index'])->name('reports.index');

    Route::middleware('role:pelapor')->group(function () {
        Route::get('/reports/create', [IncidentReportController::class, 'create'])->name('reports.create');
        Route::post('/reports', [IncidentReportController::class, 'store'])->name('reports.store');
        Route::get('/reports/{report}/edit', [IncidentReportController::class, 'edit'])->name('reports.edit');
        Route::put('/reports/{report}', [IncidentReportController::class, 'update'])->name('reports.update');
    });

    Route::get('/reports/{report}', [IncidentReportController::class, 'show'])->name('reports.show');

    Route::patch('/reports/{report}/status', [IncidentReportController::class, 'updateStatus'])
        ->middleware('role:pemantau,koordinator')
        ->name('reports.update-status');

    Route::get('/rekap', [RekapController::class, 'index'])
        ->middleware('role:pemantau,koordinator')
        ->name('rekap.index');
});
