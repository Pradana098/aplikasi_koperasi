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

    // Pengurus menyetujui dan menentukan tenor + bunga (tanpa transfer saldo)
    public function setujuiPinjaman(Request $request, $id)
    {
        $request->validate([
            'cicilan' => 'required|array|min:1',
            'cicilan.*.tanggal_jatuh_tempo' => 'required|date|after_or_equal:today',
            'cicilan*.bulan_ke' => 'required|integer|min:1',
            'cicilan..*.jumlah_cicilan' => 'required|numeric|min:1000',
        ]);

        $pinjaman = Pinjaman::findOrFail($pinjaman_id);

        DB::beginTransaction();
        try {
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

            $pinjaman->update([
                'tenor' => $request->tenor,
                'bunga' => $request->bunga,
                'status' => 'disetujui',
            ]);

            $jumlahPinjaman = $pinjaman->jumlah_pinjaman;
            $tenor = $request->tenor;
            $bunga = $request->bunga;

            // Total pengembalian
            $totalBunga = ($bunga / 100) * $jumlahPinjaman * $tenor;
            $totalPengembalian = $jumlahPinjaman + $totalBunga;
            $cicilanBulanan = round($totalPengembalian / $tenor, 2);

            // Buat cicilan otomatis
            $tanggalMulai = Carbon::now()->startOfMonth()->addMonth();
            for ($i = 1; $i <= $tenor; $i++) {
                Cicilan::create([
                    'pinjaman_id' => $pinjaman->id,
                    'bulan_ke' => $i,
                    'tanggal_jatuh_tempo' => $tanggalMulai->copy()->addMonths($i - 1)->endOfMonth(),
                    'jumlah_cicilan' => $cicilanBulanan,
                    'status' => 'belum_lunas',
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Pinjaman disetujui & cicilan dibuat otomatis. Silakan lakukan transfer saldo secara manual']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menambahkan cicilan', 'error' => $e->getMessage()], 500);
        }
    }

    // Menampilkan semua pinjaman (admin/pengurus)
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
        $pinjaman = Pinjaman::where('user_id', auth()->id())->with('cicilan')->get();
        return response()->json($pinjaman);
    }

    // Fungsi tolak pinjaman (bisa kamu tambahkan jika ingin)
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
