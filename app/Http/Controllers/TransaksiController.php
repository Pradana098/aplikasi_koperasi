<?php

namespace App\Http\Controllers;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TransaksiController extends Controller
{
    public function transaksi(Request $request)
{
    $anggotaId = $request->user()->id;

    $transaksi = Transaksi::where('anggota_id', $anggotaId)
                ->orderBy('tanggal', 'desc')
                ->get();

    return response()->json($transaksi);
}

public function store(Request $request)
{
    $request->validate([
        'tipe' => 'required|string',
        'deskripsi' => 'nullable|string',
        'jumlah' => 'required|integer',
        'tanggal' => 'required|date',
    ]);

    $transaksi = Transaksi::create([
        'anggota_id' => $request->user()->id,
        'tipe' => $request->tipe,
        'deskripsi' => $request->deskripsi,
        'jumlah' => $request->jumlah,
        'tanggal' => $request->tanggal,
    ]);

    return response()->json(['message' => 'Transaksi dicatat.', 'data' => $transaksi], 201);
}

}
