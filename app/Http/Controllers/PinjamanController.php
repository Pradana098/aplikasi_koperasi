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

<<<<<<< HEAD
    // Pengurus menyetujui dan menentukan tenor + bunga (tanpa transfer saldo)
    public function setujuiPinjaman(Request $request, $id)
=======
    // Pengurus menyetujui pinjaman dan menentukan tenor + bunga (tanpa cicilan otomatis)
   public function setujuiPinjaman(Request $request, $id)
{
    $request->validate([
        'tenor' => 'required|integer|min:1',
        'bunga' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();

    try {
        // Cari pinjaman berdasarkan ID
        $pinjaman = Pinjaman::find($id);

        // Jika tidak ditemukan, kembalikan respons error
        if (!$pinjaman) {
            return response()->json([
                'message' => 'Pinjaman tidak ditemukan',
                'id_yang_dicari' => $id
            ], 404);
        }

        // Cek status pinjaman
        if ($pinjaman->status !== 'menunggu') {
            return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
        }

        // Update data pinjaman
        $pinjaman->update([
            'tenor' => $request->tenor,
            'bunga' => $request->bunga,
            'status' => 'disetujui',
        ]);

        // Tambahkan saldo ke user
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


    // Pengurus membuat cicilan manual untuk pinjaman (bisa batch atau satu per satu)
    public function tambahCicilanManual(Request $request, $pinjaman_id)
>>>>>>> 994cf37d038f034cfb8663b3e3241972e8675934
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

<<<<<<< HEAD
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
=======
            DB::commit();
            return response()->json(['message' => 'Cicilan berhasil ditambahkan atau diperbarui']);
>>>>>>> 994cf37d038f034cfb8663b3e3241972e8675934
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
