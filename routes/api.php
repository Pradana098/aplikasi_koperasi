<?php

use App\Http\Controllers\PengurusController;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
Route::post('/buat-password/{token}', [AuthController::class, 'submitPassword']);
Route::get('/sk-file/{id}', [AuthController::class, 'getSKFile']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route untuk role pengawas
    Route::middleware('role:pengawas')->group(function () {
        Route::get('/dashboard/pengawas', [PengawasController::class, 'index']);
    });

    // Route untuk role pengurus
    Route::middleware('role:pengurus')->group(function () {
        Route::get('/dashboard/pengurus', [PengurusController::class, 'index']);
        Route::get('/pending', [PengurusController::class, 'listPendingAnggota']);
        Route::get('/anggota/aktif',[PengurusController::class, 'listAnggotaAktif']);
        Route::get('anggota/ditolak', [PengurusController::class,'listAnggotaDitolak']);
        Route::post('/anggota/verifikasi/{id}', [PengurusController::class, 'verifikasi']);

        Route::get('/pengurus/jumlah-anggota', [PengurusController::class, 'jumlahAnggota']);
    });

    // Route untuk role anggota
    Route::middleware('role:anggota')->group(function () {
        Route::get('/dashboard/anggota', [AnggotaController::class, 'index']);
        Route::get('/anggota/status', [AnggotaController::class, 'statusPendaftaranSaya']);
    });
});
