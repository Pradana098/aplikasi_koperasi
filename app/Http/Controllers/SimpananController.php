<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Simpanan;

class SimpananController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:anggota|pengurus']);
    }

    // Menampilkan semua simpanan user (bisa filter tipe)
    public function index(Request $request)
    {
        $query = Simpanan::where('user_id', $request->user()->id);

        if ($request->has('tipe')) {
            $query->where('tipe', $request->tipe); // 'rutin' atau 'non_rutin'
        }

        $simpanan = $query->get();
        return response()->json($simpanan);
    }

    // Menyimpan simpanan baru
    public function store(Request $request)
    {
        $request->validate([
            'jenis_simpanan_id' => 'required|exists:jenis_simpanan,id',
            'tipe' => 'required|in:rutin,non_rutin',
            'jumlah' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        $simpanan = Simpanan::create([
            'user_id' => $request->user()->id,
            'jenis_simpanan_id' => $request->jenis_simpanan_id,
            'tipe' => $request->tipe,
            'jumlah' => $request->jumlah,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json($simpanan, 201);
    }
}
