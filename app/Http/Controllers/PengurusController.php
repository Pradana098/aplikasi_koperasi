<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use App\Notifications\NotifikasiHelper;

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

   public function PendingAnggota()
    {
        return response()->json(['message' => 'Berhasil ambil anggota pending']);
    }


    public function approveAnggota($id)
    {
        try {
            $anggota = User::where('id', $id)->where('role', 'anggota')->firstOrFail();
            $anggota->status = 'aktif';
            $anggota->save();

            return response()->json([
                'status' => true,
                'message' => 'Anggota berhasil disetujui.',
                'data' => $anggota
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Anggota tidak ditemukan.'
            ], 404);
        }
    }

    public function rejectAnggota($id)
    {
        try {
            $anggota = User::where('id', $id)->where('role', 'anggota')->firstOrFail();
            $anggota->status = 'ditolak';
            $anggota->save();

            return response()->json([
                'status' => true,
                'message' => 'Anggota berhasil ditolak.',
                'data' => $anggota
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Anggota tidak ditemukan.'
            ], 404);
        }
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
