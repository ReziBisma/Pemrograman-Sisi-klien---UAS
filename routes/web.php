<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminPoliController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\PasienController;
use App\Http\Controllers\Admin\ObatController;
use App\Http\Controllers\Dokter\JadwalPeriksaController;
use App\Http\Controllers\Pasien\PoliController;
use App\Http\Controllers\Dokter\PeriksaPasienController;
use App\Http\Controllers\Dokter\RiwayatPasienController;
use App\Http\Controllers\Pasien\TransaksiController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    Route::view('/polis', 'admin.polis.index')->name('polis.index');
    Route::view('/polis/create', 'admin.polis.create')->name('polis.create');
    Route::get('/polis/{id}/edit', function ($id) {
        return view('admin.polis.edit', ['id' => $id]);
    })->whereNumber('id')->name('polis.edit');
    Route::view('/obat', 'admin.obat.index')->name('obat.index');
    Route::view('/obat/create', 'admin.obat.create')->name('obat.create');
    Route::get('/obat/{id}/edit', function ($id) {
        return view('admin.obat.edit', ['id' => $id]);
    })->whereNumber('id')->name('obat.edit');

    // Dokter
    Route::view('/dokter', 'admin.dokter.index')->name('dokter.index');
    Route::view('/dokter/create', 'admin.dokter.create')->name('dokter.create');
    Route::get('/dokter/{id}/edit', function ($id) {
        return view('admin.dokter.edit', ['id' => $id]);
    })->whereNumber('id')->name('dokter.edit');

    // Pasien
    Route::view('/pasien', 'admin.pasien.index')->name('pasien.index');
    Route::view('/pasien/create', 'admin.pasien.create')->name('pasien.create');
    Route::get('/pasien/{id}/edit', function ($id) {
        return view('admin.pasien.edit', ['id' => $id]);
    })->whereNumber('id')->name('pasien.edit');
});


Route::middleware(['auth', 'role:dokter'])->prefix('dokter')->group(function () {
    Route::get('/dashboard', function () {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');

        // Jadwal periksa
    Route::view('/jadwal-periksa', 'dokter.jadwal-periksa.index')->name('jadwal-periksa.index');
    Route::view('/jadwal-periksa/create', 'dokter.jadwal-periksa.create')->name('jadwal-periksa.create');
    Route::get('/jadwal-periksa/{id}/edit', function ($id) {
        return view('dokter.jadwal-periksa.edit', ['id' => $id]);
    })->whereNumber('id')->name('jadwal-periksa.edit');

    // Periksa pasien
    Route::view('/periksa-pasien', 'dokter.periksa-pasien.index')->name('periksa-pasien.index');
    Route::get('/periksa-pasien/{id}', function ($id) {
        return view('dokter.periksa-pasien.create', ['id' => $id]);
    })->whereNumber('id')->name('periksa-pasien.create');

    // Riwayat pasien
    Route::view('/riwayat-pasien', 'dokter.riwayat-pasien.index')->name('riwayat-pasien.index');
    Route::get('/riwayat-pasien/{id}', function ($id) {
        return view('dokter.riwayat-pasien.show', ['id' => $id]);
    })->whereNumber('id')->name('riwayat-pasien.show');
});


Route::middleware(['auth', 'role:pasien'])->prefix('pasien')->group(function () {
    Route::get('/dashboard', function () {
        return view('pasien.dashboard');
    })->name('pasien.dashboard');

    // Pendaftaran poli
    Route::view('/daftar', 'pasien.daftar')->name('pasien.daftar');

    // Transaksi
    Route::view('/transaksi', 'pasien.transaksi.index')->name('pasien.transaksi');
    Route::get('/transaksi/{id}', function ($id) {
        return view('pasien.transaksi.show', ['id' => $id]);
    })->whereNumber('id')->name('pasien.transaksi.show');
});
