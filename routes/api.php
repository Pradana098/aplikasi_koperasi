<?php

use App\Http\Controllers\PengurusController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\AnggotaManajemenController;
use App\Http\Controllers\LaporanSimpananController;
use Illuminate\Http\Request;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
Route::post('/buat-password/{token}', [AuthController::class, 'submitPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
    Route::get('/laporan-simpanan', [LaporanSimpananController::class, 'index']);

    // Route untuk role pengawas
    Route::middleware('role:pengawas')->group(function () {
        Route::get('/dashboard/pengawas', [PengawasController::class, 'index']);
    });

    // Route untuk role pengurus
    Route::middleware('role:pengurus')->group(function () {
        Route::get('/dashboard/pengurus', [PengurusController::class, 'index']);
        Route::get('/status/{status}', [PengurusController::class, 'getAnggotaByStatus']);
        Route::post('/anggota/verifikasi/{id}', [PengurusController::class, 'verifikasi']);
        Route::get('/pengurus/jumlah-anggota', [PengurusController::class, 'jumlahAnggota']);
        route::get('/pengurus/notifikasi', [PengurusController::class, 'listNotifikasi']);
        route::get('/pengurus/simpanan/riwayat', [PengurusController::class, 'semuaRiwayatSimpanan']);
        Route::post('/pengurus/pinjaman/ajukan', [PinjamanController::class, 'ajukanPinjaman']);
        Route::get('/pengurus/pinjaman/pengajuan', [PinjamanController::class, 'daftarPengajuan']);
        Route::post('/pengurus/pinjaman/proses/{id}', [PinjamanController::class, 'prosesPengajuan']);
    });


    // Route untuk role anggota
    Route::middleware('role:anggota')->group(function () {
        Route::get('/dashboard/anggota', [AnggotaController::class, 'index']);
        Route::get('/anggota/status', [AnggotaController::class, 'statusPendaftaranSaya']);
        Route::get('/riwayat-simpanan-wajib/{user_id}', [AnggotaController::class, 'riwayatWajib']);
        Route::get('/anggota/notifikasi', [AnggotaController::class, 'listNotifikasi']);
        Route::get('/anggota/simpanan-wajib', [AnggotaController::class, 'SimpananWajib']);
        Route::post('/anggota/input/simpanan-sukarela', [AnggotaController::class, 'aturPotonganSukarela']);
        Route::get('/anggota/riwayat-simpanan-sukarela', [AnggotaController::class, 'riwayatRutin']);
    });

    // Untuk Pengurus
    Route::middleware(['auth:sanctum', 'role:pengurus'])->group(function () {
        Route::get('/pengurus/anggota', [AnggotaManajemenController::class, 'index']);
        Route::post('/pengurus/anggota', [AnggotaManajemenController::class, 'store']);
        Route::put('/pengurus/anggota/{id}', [AnggotaManajemenController::class, 'update']);
        Route::delete('/pengurus/anggota/{id}', [AnggotaManajemenController::class, 'destroy']);
    });

    // Untuk Pengawas
    Route::middleware(['auth:sanctum', 'role:pengawas'])->group(function () {
        Route::get('/pengawas/anggota/{id}', [AnggotaManajemenController::class, 'show']);
        Route::get('/pengawas/anggota/export/excel', [AnggotaManajemenController::class, 'exportExcel']);
        Route::get('/pengawas/anggota/export/pdf', [AnggotaManajemenController::class, 'exportPDF']);
    });



});
