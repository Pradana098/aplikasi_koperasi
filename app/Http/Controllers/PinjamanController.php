<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Cicilan;
use App\Models\User;
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
            $pinjaman = Pinjaman::find($id);

            if (!$pinjaman) {
                return response()->json([
                    'message' => 'Pinjaman tidak ditemukan',
                    'id_yang_dicari' => $id
                ], 404);
            }

            if ($pinjaman->status !== 'menunggu') {
                return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
            }

            $pinjaman->update([
                'tenor' => $request->tenor,
                'bunga' => $request->bunga,
                'status' => 'disetujui',
            ]);

            $user = User::find($pinjaman->user_id);
            if ($user) {
                $user->saldo += $pinjaman->jumlah_pinjaman;
                $user->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Pinjaman disetujui. Silakan buat cicilan manual sesuai kebutuhan.',
                'pinjaman_id' => $pinjaman->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyetujui pinjaman',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Pengurus membuat cicilan manual untuk pinjaman
    public function tambahCicilanManual(Request $request, $pinjaman_id)

    // Pengurus menyetujui pinjaman dan menambahkan cicilan manual
    public function setujuiPinjaman(Request $request, $id)

    {
        $request->validate([
            'tenor' => 'required|integer|min:1',
            'bunga' => 'required|numeric|min:0',
            'cicilan' => 'required|array|min:1',
            'cicilan.*.bulan_ke' => 'required|integer|min:1',
            'cicilan.*.tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',

            'cicilan.*.bulan_ke' => 'required|integer|min:1',


            'cicilan.*.jumlah_cicilan' => 'required|numeric|min:1000',
        ]);

        DB::beginTransaction();

        try {
            $pinjaman = Pinjaman::findOrFail($id);

            if ($pinjaman->status !== 'menunggu') {
                return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
            }

            // Update status dan detail pinjaman
            $pinjaman->update([
                'tenor' => $request->tenor,
                'bunga' => $request->bunga,
                'status' => 'disetujui',
            ]);

            // Simpan cicilan yang dimasukkan oleh pengurus
            foreach ($request->cicilan as $data) {
                Cicilan::updateOrCreate(
                    ['pinjaman_id' => $pinjaman->id, 'bulan_ke' => $data['bulan_ke']],
                    [
                        'tanggal_jatuh_tempo' => $data['tanggal_jatuh_tempo'],
                        'jumlah_cicilan' => $data['jumlah_cicilan'],
                        'status' => 'belum_lunas',
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Pinjaman disetujui dan cicilan berhasil ditambahkan secara manual.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyetujui pinjaman',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Menampilkan semua pinjaman yang menunggu disetujui
    public function daftarPengajuan()
    {
        $data = Pinjaman::with('user')
            ->where('status', 'menunggu')
            ->get();

        return response()->json(['data' => $data]);
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
        $pinjaman = Pinjaman::where('user_id', auth()->id())
            ->with('cicilan')
            ->get();

        return response()->json($pinjaman);
    }


    // Fungsi tolak pinjaman

    // Fungsi untuk menolak pengajuan pinjaman

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
