<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengurusController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AnggotaManajemenController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\LaporanSimpananController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;
use App\Http\Controllers\TransaksiController;

// 🔐 Auth Routes (Tanpa Middleware)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
Route::post('/buat-password/{token}', [AuthController::class, 'submitPassword']);

// 🛡 Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // 🔒 Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);

    // 📊 Laporan
    Route::get('/laporan-simpanan', [LaporanSimpananController::class, 'LaporanSimpanan']);

    // ======================
    // 👮 PENGAWAS ROUTES
    // ======================
    Route::middleware('role:pengawas')->group(function () {
        Route::get('/dashboard/pengawas', [PengawasController::class, 'index']);
        Route::get('/pengawas/anggota/{id}', [AnggotaManajemenController::class, 'show']);
        Route::get('/pengawas/anggota/export/excel', [AnggotaManajemenController::class, 'exportExcel']);
        Route::get('/pengawas/anggota/export/pdf', [AnggotaManajemenController::class, 'exportPDF']);
    });

    // ======================
    // 👨‍💼 PENGURUS ROUTES
    // ======================
    Route::middleware('role:pengurus')->group(function () {
        // Dashboard & Anggota
        Route::get('/dashboard/pengurus', [PengurusController::class, 'index']);
        Route::get('/status/{status}', [PengurusController::class, 'getAnggotaByStatus']);
        Route::post('/anggota/verifikasi/{id}', [PengurusController::class, 'verifikasi']);
        Route::get('/pengurus/jumlah-anggota', [PengurusController::class, 'jumlahAnggota']);
        Route::get('/pengurus/notifikasi', [PengurusController::class, 'listNotifikasi']);
        Route::get('/pengurus/simpanan/riwayat', [PengurusController::class, 'semuaRiwayatSimpanan']);

        // Pinjaman
        Route::get('/pinjaman/pengajuan', [PinjamanController::class, 'daftarPengajuan']);
        Route::get('/pinjaman/{id}/detail', [PinjamanController::class, 'detailPinjaman']);
        Route::post('/pinjaman/{id}/setujui-dengan-cicilan', [PinjamanController::class, 'setujuiPinjamanSekaligusCicilan']);
        Route::post('/pinjaman/{id}/tolak', [PinjamanController::class, 'tolakPinjaman']);
        Route::post('/pinjaman/{id}/transfer', [PinjamanController::class, 'transferSaldo']);
        Route::post('/pinjaman/{id}/cicilan/manual', [PinjamanController::class, 'tambahCicilanManual']);

        // Manajemen Anggota
        Route::get('/pengurus/anggota', [AnggotaManajemenController::class, 'index']);
        Route::post('/pengurus/anggota', [AnggotaManajemenController::class, 'store']);
        Route::put('/pengurus/anggota/{id}', [AnggotaManajemenController::class, 'update']);
        Route::delete('/pengurus/anggota/{id}', [AnggotaManajemenController::class, 'destroy']);
    });

    // ======================
    // 👤 ANGGOTA ROUTES
    // ======================
    Route::middleware('role:anggota')->group(function () {
        Route::get('/dashboard/anggota', [AnggotaController::class, 'index']);
        Route::get('/anggota/status', [AnggotaController::class, 'statusPendaftaranSaya']);
        Route::get('/anggota/notifikasi', [AnggotaController::class, 'listNotifikasi']);
        Route::get('/anggota/simpanan-wajib', [AnggotaController::class, 'SimpananWajib']);
        Route::get('/riwayat-simpanan-wajib/{user_id}', [AnggotaController::class, 'riwayatWajib']);
        Route::get('/anggota/riwayat-simpanan-sukarela', [AnggotaController::class, 'riwayatRutin']);
        Route::post('/anggota/input/simpanan-sukarela', [AnggotaController::class, 'aturPotonganSukarela']);

        // Pinjaman Anggota
        Route::post('/pinjaman/ajukan', [PinjamanController::class, 'ajukanPinjaman']);
        Route::get('/pinjaman/saya', [PinjamanController::class, 'pinjamanSaya']);

        // Riwayat Transaksi Anggota
        Route::get('/transaksi', [TransaksiController::class, 'transaksi']);
        Route::post('/transaksi', [TransaksiController::class, 'store']);
    });

    // ======================
    // 💰 Simpanan
    // ======================
    Route::prefix('simpanan')->group(function () {
        Route::get('/', [SimpananController::class, 'index']);
        // Tambahkan route laporan jika perlu diaktifkan nanti
        // Route::get('/laporan', [LaporanSimpananController::class, 'index']);
    });

});
