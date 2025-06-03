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
use App\Http\Controllers\SimpananController;
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
    Route::get('/laporan-simpanan', [LaporanSimpananController::class, 'LaporanSimpanan']);

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
        Route::get('/pinjaman/pengajuan', [PinjamanController::class, 'daftarPengajuan']);
        Route::post('/pinjaman/{id}/setujui', [PinjamanController::class, 'setujuiPinjaman']);
        Route::post('/pinjaman/{id}/tolak', [PinjamanController::class, 'tolakPinjaman']);
        Route::post('/pinjaman/{id}/transfer', [PinjamanController::class, 'transferSaldo']);
        Route::get('/pinjaman/{id}/detail', [PinjamanController::class, 'detailPinjaman']);
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
        Route::post('/pinjaman/ajukan', [PinjamanController::class, 'ajukanPinjaman']);
        Route::get('/pinjaman/saya', [PinjamanController::class, 'pinjamanSaya']);
        
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

    // Untuk Anggota
    Route::middleware(['auth:sanctum', 'role:anggota'])->group(function () {
        Route::post('/pinjaman/ajukan', [PinjamanController::class, 'ajukanPinjaman']);
        Route::get('/pinjaman/saya', [PinjamanController::class, 'pinjamanSaya']);
    });

      Route::prefix('simpanan')->group(function () {
        Route::get('/', [SimpananController::class, 'index']);
        // Route::get('/laporan', [LaporanSimpananController::class, 'index']);
    });




});
