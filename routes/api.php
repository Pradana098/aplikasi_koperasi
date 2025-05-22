<?php

use App\Http\Controllers\PengurusController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

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
        route::get('/pengurus/notifikasi',[PengurusController::class, 'listNotifikasi']);
        route::get('/pengurus/simpanan/riwayat', [PengurusController::class, 'semuaRiwayatSimpanan']);
    });


    // Route untuk role anggota
    Route::middleware('role:anggota')->group(function () {
        Route::get('/dashboard/anggota', [AnggotaController::class, 'index']);
        Route::get('/anggota/status', [AnggotaController::class, 'statusPendaftaranSaya']);
        Route::get('/riwayat-simpanan-wajib/{user_id}', [AnggotaController::class, 'riwayatWajib']);
        Route::get('/anggota/notifikasi',[AnggotaController::class, 'listNotifikasi']);
        Route::get('/anggota/simpanan/wajib', [AnggotaController::class, 'SimpananWajib']);

    });

    // route untuk semua role update profile
    Route::middleware('auth:sanctum')->put('/user/profile', [UserController::class, 'updateProfile']);


});
