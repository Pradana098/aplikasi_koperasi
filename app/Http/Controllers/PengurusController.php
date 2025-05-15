<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Anggota;
use Illuminate\Support\Str;
use App\Notifications\NotifikasiHelper;
use Illuminate\Support\Facades\Log;

class PengurusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:pengurus']);
    }

    public function index()
    {
        return response()->json([
            'message' => 'Selamat datang di dashboard pengurus'
        ]);
    }


    public function getAnggotaByStatus($status)
    {
        try {
            // Validasi status yang diterima
            if (!in_array($status, ['pending', 'aktif', 'ditolak'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status tidak valid. Status harus berupa: pending, aktif, atau ditolak'
                ], 400);
            }

            // Ambil data anggota berdasarkan status
            $anggota = User::where('role', 'anggota')
                        ->where('status', $status)
                        ->orderBy('created_at', 'desc')
                        ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data anggota berhasil diambil',
                'data' => $anggota
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error mengambil data anggota: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data anggota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,ditolak',
        ]);

        $anggota = User::find($id);

        if (!$anggota || $anggota->role !== 'anggota') {
            return response()->json(['error' => 'Anggota tidak ditemukan'], 404);
        }

        $anggota->status = $request->status;
        $anggota->save();

        return response()->json(['message' => 'Status berhasil diperbarui']);
    }


    public function jumlahAnggota()
    {
        $jumlah = User::where('role', 'anggota')->count();
        return response()->json([
            'status' => true,
            'total_anggota' => $jumlah
        ]);
    }




}
