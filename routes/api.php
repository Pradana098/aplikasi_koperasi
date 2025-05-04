<?php

use App\Http\Controllers\PengurusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\API\Auth\ForgotPasswordController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function () {
    return response(['pesan' => 'API siap']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route untuk role pengawas
    Route::middleware('role:pengawas')->group(function () {
        Route::get('/dashboard/pengawas', [PengawasController::class, 'index']);
    });

    // Route untuk role pengurus
    Route::middleware('role:pengurus')->group(function () {
        Route::get('/dashboard/pengurus', [PengurusController::class, 'index']);
        Route::get('/anggota/pending', [PengurusController::class, 'listPendingAnggota']);
        Route::post('/anggota/{id}/approve', [PengurusController::class, 'approveAnggota']);
        Route::post('/anggota/{id}/reject', [PengurusController::class, 'rejectAnggota']);
        Route::get('/pengurus/jumlah-anggota', [PengurusController::class, 'jumlahAnggota']);
        Route::get('/anggota/{id}/status', [PengurusController::class, 'detailStatusPendaftaran']);
    });

    // Route untuk role anggota
    Route::middleware('role:anggota')->group(function () {
        Route::get('/dashboard/anggota', [AnggotaController::class, 'index']);
        Route::get('/anggota/status', [AnggotaController::class, 'statusPendaftaranSaya']);
    });
});
