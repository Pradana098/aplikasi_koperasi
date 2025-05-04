<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnggotaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:anggota']);
    }

    public function index()
    {
        return response()->json([
            'message' => 'Selamat datang di dashboard anggota',
        ]);
    }

    public function statusPendaftaranSaya(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $message = match ($user->status) {
            'aktif' => 'Pendaftaran Berhasil Diterima',
            'ditolak' => 'Pendaftaran Ditolak',
            'pending' => 'Menunggu Persetujuan',
            default => 'Status tidak diketahui',
        };

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'status' => $user->status,
                'message' => $message,
            ]
        ]);
    }
}
