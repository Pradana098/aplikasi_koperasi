<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Cicilan;
use Illuminate\Support\Facades\DB;

class PinjamanController extends Controller
{
    // Anggota mengajukan pinjaman
    public function ajukanPinjaman(Request $request)
    {
        $request->validate([
            'jumlah_pinjaman' => 'required|numeric', // Tanpa batasan minimum
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

    // Pengurus menyetujui pinjaman dan menambahkan tenor, bunga, serta cicilan (tanpa transfer saldo)
    public function setujuiPinjamanDenganCicilan(Request $request, $id)
    {
        $request->validate([
            'tenor' => 'required|integer|min:1',
            'bunga' => 'required|numeric|min:0',
            'cicilan' => 'required|array|min:1',
            'cicilan.*.bulan_ke' => 'required|integer|min:1',
            'cicilan.*.tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
            'cicilan.*.jumlah_cicilan' => 'required|numeric|min:1000',
        ]);

        DB::beginTransaction();

        try {
            $pinjaman = Pinjaman::findOrFail($id);

            if ($pinjaman->status !== 'menunggu') {
                return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
            }

            // Update data pinjaman
            $pinjaman->update([
                'tenor' => $request->tenor,
                'bunga' => $request->bunga,
                'status' => 'disetujui',
            ]);

            // Simpan cicilan
            foreach ($request->cicilan as $data) {
                Cicilan::create([
                    'pinjaman_id' => $pinjaman->id,
                    'bulan_ke' => $data['bulan_ke'],
                    'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
                    'jumlah_cicilan' => $data['jumlah_cicilan'],
                    'status' => 'belum_lunas',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pinjaman disetujui dan cicilan berhasil ditambahkan.',
                'pinjaman_id' => $pinjaman->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memproses pinjaman',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function daftarPengajuan()
    {
        $data = Pinjaman::with('user')
            ->where('status', 'menunggu')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function detailPinjaman($id)
    {
        $pinjaman = Pinjaman::with(['user', 'cicilan'])->findOrFail($id);
        return response()->json($pinjaman);
    }

    public function pinjamanSaya()
    {
        $pinjaman = Pinjaman::where('user_id', auth()->id())
            ->with('cicilan')
            ->get();

        return response()->json($pinjaman);
    }

    public function tolakPinjaman($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);

        if ($pinjaman->status !== 'menunggu') {
            return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
        }

        $pinjaman->update([
            'status' => 'ditolak',
        ]);

        return response()->json(['message' => 'Pengajuan pinjaman telah ditolak']);
    }
}
