<?php

use App\Http\Controllers\Api\Admin\DokterController;
use App\Http\Controllers\Api\Admin\ObatController;
use App\Http\Controllers\Api\Admin\PasienController;
use App\Http\Controllers\Api\Admin\PoliController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Api\Dokter\PeriksaPasienController;
use App\Http\Controllers\Api\Dokter\RiwayatPasienController;
use App\Http\Controllers\Api\Pasien\PendaftaranController;
use App\Http\Controllers\Api\Pasien\TransaksiController;
use Illuminate\Support\Facades\Route;

// ===== Publik =====
// ===== Publik =====
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:api-login')->name('api.register');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:api-login')->name('api.login');

// ===== Harus login (token Sanctum atau sesi web) =====
Route::middleware(['auth:sanctum', 'throttle:api-pengguna'])->name('api.')->group(function () {
    // ... isi grup tidak berubah

    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/logout-semua', [AuthController::class, 'logoutSemua'])->name('logout-semua');

    // ----- Admin -----
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('polis', PoliController::class);
        Route::apiResource('dokter', DokterController::class);
        Route::apiResource('pasien', PasienController::class);
        Route::apiResource('obat', ObatController::class);
    });

    // ----- Dokter -----
    Route::middleware('role:dokter')->prefix('dokter')->name('dokter.')->group(function () {
        Route::apiResource('jadwal-periksa', JadwalPeriksaController::class);

        Route::get('periksa-pasien', [PeriksaPasienController::class, 'index'])->name('periksa-pasien.index');
        Route::post('periksa-pasien', [PeriksaPasienController::class, 'store'])->name('periksa-pasien.store');

        Route::get('riwayat-pasien', [RiwayatPasienController::class, 'index'])->name('riwayat-pasien.index');
        Route::get('riwayat-pasien/{id}', [RiwayatPasienController::class, 'show'])->name('riwayat-pasien.show');
        Route::get('obat', [PeriksaPasienController::class, 'obat'])->name('obat');
    });

    // ----- Pasien -----
    Route::middleware('role:pasien')->prefix('pasien')->name('pasien.')->group(function () {
        Route::get('poli', [PendaftaranController::class, 'poli'])->name('poli');
        Route::get('pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
        Route::post('pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');

        Route::get('transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('transaksi/{id}', [TransaksiController::class, 'show'])->name('transaksi.show');
        Route::post('transaksi/{id}/bayar', [TransaksiController::class, 'bayar'])->name('transaksi.bayar');
    });
});