<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Cicilan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PinjamanController extends Controller
{
    // Anggota mengajukan pinjaman
    public function ajukanPinjaman(Request $request)
    {
        $request->validate([
            'jumlah_pinjaman' => 'required|numeric|min:100000',
            'tanggal_pengajuan' => 'required|date',
        ]);

        $pinjaman = Pinjaman::create([
            'user_id' => auth()->id(),
            'jumlah_pinjaman' => $request->jumlah_pinjaman,
            'tanggal_pengajuan' => $request->tanggal_pengajuan,
            'status' => 'menunggu',
        ]);

        return response()->json(['message' => 'Pengajuan pinjaman berhasil dikirim', 'data' => $pinjaman]);
    }

    // Pengurus menyetujui pinjaman dan menentukan tenor + bunga (tanpa cicilan otomatis)
    public function setujuiPinjaman(Request $request, $id)
    {
        $request->validate([
            'tenor' => 'required|integer|min:1',
            'bunga' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::findOrFail($id);

            if ($pinjaman->status !== 'menunggu') {
                return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
            }

            $pinjaman->update([
                'tenor' => $request->tenor,
                'bunga' => $request->bunga,
                'status' => 'disetujui',
            ]);

            // Transfer saldo ke anggota
            $user = User::find($pinjaman->user_id);
            $user->saldo += $pinjaman->jumlah_pinjaman;
            $user->save();

            DB::commit();

            return response()->json([
                'message' => 'Pinjaman disetujui. Silakan buat cicilan manual sesuai kebutuhan.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal menyetujui pinjaman', 'error' => $e->getMessage()], 500);
        }
    }

    // Pengurus membuat cicilan manual untuk pinjaman
    public function tambahCicilanManual(Request $request, $pinjaman_id)
    {
        $request->validate([
            'bulan_ke' => 'required|integer|min:1',
            'tanggal_jatuh_tempo' => 'required|date',
            'jumlah_cicilan' => 'required|numeric|min:1000',
        ]);

        $pinjaman = Pinjaman::findOrFail($pinjaman_id);

        $cicilan = Cicilan::create([
            'pinjaman_id' => $pinjaman->id,
            'bulan_ke' => $request->bulan_ke,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'jumlah_cicilan' => $request->jumlah_cicilan,
            'status' => 'belum_lunas',
        ]);

        return response()->json(['message' => 'Cicilan berhasil ditambahkan', 'data' => $cicilan]);
    }

    // Menampilkan semua pinjaman (admin/pengurus)
    public function daftarPengajuan()
    {
        $pinjaman = Pinjaman::with('user')->latest()->get();
        return response()->json($pinjaman);
    }

    // Detail pinjaman & cicilan
    public function detailPinjaman($id)
    {
        $pinjaman = Pinjaman::with(['user', 'cicilan'])->findOrFail($id);
        return response()->json($pinjaman);
    }

    // Anggota bisa melihat pinjamannya sendiri
    public function pinjamanSaya()
    {
        $pinjaman = Pinjaman::where('user_id', auth()->id())->with('cicilan')->get();
        return response()->json($pinjaman);
    }
}
