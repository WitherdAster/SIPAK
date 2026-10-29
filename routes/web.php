<?php

use App\Http\Controllers\AplikasiController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PerubahanAplikasiController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Support\Facades\Route;

// Halaman tamu (sudah login otomatis ke dashboard)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

// Halaman terproteksi (harus login)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/aplikasi', [AplikasiController::class, 'index'])->name('aplikasi.index');
    Route::get('/aplikasi/{aplikasi}', [AplikasiController::class, 'show'])->name('aplikasi.show');
    Route::get('/aplikasiku', [AplikasiController::class, 'milikku'])->name('aplikasi.milikku');
    Route::get('/pengajuan', [AplikasiController::class, 'create'])->name('aplikasi.create');
    Route::post('/pengajuan', [AplikasiController::class, 'store'])->name('aplikasi.store');

    // Edit data: admin untuk semua aplikasi, operator hanya milik instansinya
    Route::get('/aplikasi/{aplikasi}/edit', [AplikasiController::class, 'edit'])->name('aplikasi.edit');
    Route::put('/aplikasi/{aplikasi}', [AplikasiController::class, 'update'])->name('aplikasi.update');
    Route::post('/aplikasi/{aplikasi}/perubahan/batal', [AplikasiController::class, 'batalkanPerubahan'])->name('aplikasi.perubahan.batal');
    Route::get('/dokumen/{dokumen}/lihat', [DokumenController::class, 'lihat'])->name('dokumen.lihat');
    Route::get('/dokumen/{dokumen}/unduh', [DokumenController::class, 'unduh'])->name('dokumen.unduh');

    // Ubah status: admin semua aplikasi, operator hanya milik instansinya
    Route::put('/aplikasi/{aplikasi}/status', [AplikasiController::class, 'updateStatus'])->name('aplikasi.status');

    // Laporan khusus aplikasiku (instansi sendiri)
    Route::get('/laporan-saya', [LaporanController::class, 'milikku'])->name('laporan.milikku');
    Route::get('/laporan-saya/unduh', [LaporanController::class, 'unduhMilikku'])->name('laporan.unduhMilikku');

    Route::get('/password-saya', [AkunController::class, 'editPasswordSendiri'])->name('profil.password.edit');
    Route::put('/password-saya', [AkunController::class, 'updatePasswordSendiri'])->name('profil.password.update');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Khusus Admin Diskominfo: verifikasi pengajuan + kelola akun SKPD
    Route::middleware('role:admin')->group(function () {
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::post('/verifikasi/{aplikasi}/terima', [VerifikasiController::class, 'terima'])->name('verifikasi.terima');
        Route::post('/verifikasi/{aplikasi}/perbaikan', [VerifikasiController::class, 'perbaikan'])->name('verifikasi.perbaikan');

        Route::get('/verifikasi/perubahan/{perubahan}', [PerubahanAplikasiController::class, 'show'])->name('verifikasi.perubahan.show');
        Route::post('/verifikasi/perubahan/{perubahan}/setujui', [PerubahanAplikasiController::class, 'setujui'])->name('verifikasi.perubahan.setujui');
        Route::post('/verifikasi/perubahan/{perubahan}/tolak', [PerubahanAplikasiController::class, 'tolak'])->name('verifikasi.perubahan.tolak');
        Route::get('/verifikasi/perubahan/{perubahan}/dokumen/{jenis}/lihat', [PerubahanAplikasiController::class, 'lihatDokumen'])->name('verifikasi.perubahan.dokumen.lihat');
        Route::get('/verifikasi/perubahan/{perubahan}/dokumen/{jenis}', [PerubahanAplikasiController::class, 'unduhDokumen'])->name('verifikasi.perubahan.dokumen');

        Route::delete('/aplikasi/{aplikasi}', [AplikasiController::class, 'destroy'])->name('aplikasi.destroy');

        Route::get('/akun', [AkunController::class, 'index'])->name('akun.index');
        Route::get('/akun/tambah', [AkunController::class, 'create'])->name('akun.create');
        Route::post('/akun', [AkunController::class, 'store'])->name('akun.store');
        Route::get('/akun/{akun}/edit', [AkunController::class, 'edit'])->name('akun.edit');
        Route::put('/akun/{akun}', [AkunController::class, 'update'])->name('akun.update');
        Route::get('/akun/{akun}/password', [AkunController::class, 'editPassword'])->name('akun.password.edit');
        Route::put('/akun/{akun}/password', [AkunController::class, 'updatePassword'])->name('akun.password.update');
        Route::delete('/akun/{akun}', [AkunController::class, 'destroy'])->name('akun.destroy');

        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/unduh', [LaporanController::class, 'unduh'])->name('laporan.unduh');
    });
});
