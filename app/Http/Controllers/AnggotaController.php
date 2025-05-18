<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use App\Models\Simpanan;

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

    public function riwayatWajib(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya anggota yang dapat melihat riwayat simpanan wajib.'
            ], 403);
        }

        $riwayat = Simpanan::where('user_id', $user->id)
            ->where('jenis', 'wajib')
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $riwayat
        ]);
    }
    public function listNotifikasi(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pengurus yang bisa melihat notifikasi.'
            ], 403);
        }

        $notifikasi = \App\Models\Notifikasi::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifikasi,
        ]);

    }
}
