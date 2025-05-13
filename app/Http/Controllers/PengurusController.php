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


    public function listPendingAnggota()
    {
        $anggota = User::where('role', 'anggota')->where('status', 'pending')->get();
        return response()->json($anggota);
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

     public function listAnggotaAktif()
    {
        $anggota = User::where('role', 'anggota')->where('status', 'aktif')->get();
        return response()->json($anggota);
    }

     public function listAnggotaDitolak()
    {
        $anggota = User::where('role', 'anggota')->where('status', 'ditolak')->get();
        return response()->json($anggota);
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
