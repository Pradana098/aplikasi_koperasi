<?php

namespace App\Http\Controllers;

use App\Models\Pinjaman;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PinjamanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

   public function ajukanPinjaman(Request $request)
{
    $request->validate([
        'jumlah' => 'required|integer|min:1000000'
    ]);

    $user = $request->user();

    $lamaCicilan = $request->jumlah > 50000000 ? 12 : 10;

    $pinjaman = Pinjaman::create([
        'user_id' => $user->id,
        'jumlah_pinjaman' => $request->jumlah,
        'tenor' => $lamaCicilan,
        'bunga' => 5,
        'status' => 'menunggu',
        'tanggal_pengajuan' => now() // Tambahkan ini
    ]);

    // Simpan notifikasi tanpa user_id
    Notifikasi::create([
        'judul' => 'Pengajuan Pinjaman Baru',
        'pesan' => 'Ada pengajuan pinjaman baru dari ' . $user->name . ', silakan cek dan proses.'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Pengajuan pinjaman berhasil disimpan dan menunggu persetujuan.',
        'data' => $pinjaman
    ]);
}

    // Melihat semua pengajuan oleh pengurus
    public function daftarPengajuan()
    {
        $pengajuan = Pinjaman::with('user')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $pengajuan
        ]);
    }

    // Persetujuan atau Penolakan Pinjaman
    public function prosesPengajuan(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak'
        ]);

        $pinjaman = Pinjaman::find($id);

        if (!$pinjaman) {
            return response()->json(['error' => 'Pengajuan tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            $pinjaman->status = $request->status;
            $pinjaman->save();

            $anggota = User::find($pinjaman->user_id);

            if ($request->status == 'disetujui') {
                // Tambahkan saldo ke anggota
                $anggota->saldo += $pinjaman->jumlah_pinjaman;
                $anggota->save();

                // Kirim notifikasi
                Notifikasi::create([
                    'user_id' => $anggota->id,
                    'judul' => 'Pinjaman Disetujui',
                    'pesan' => 'Pengajuan pinjaman Anda sebesar Rp' . number_format($pinjaman->jumlah_pinjaman, 0, ',', '.') . ' telah disetujui.'
                ]);
            } else {
                // Kirim notifikasi penolakan
                Notifikasi::create([
                    'user_id' => $anggota->id,
                    'judul' => 'Pinjaman Ditolak',
                    'pesan' => 'Pengajuan pinjaman Anda telah ditolak oleh pengurus.'
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Status pinjaman berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memproses pinjaman: ' . $e->getMessage()], 500);
        }
    }
}
