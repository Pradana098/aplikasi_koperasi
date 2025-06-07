<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Cicilan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PinjamanController extends Controller
{
    // Anggota mengajukan pinjaman
    public function ajukanPinjaman(Request $request)
    {
        $request->validate([
            'jumlah_pinjaman' => 'required|numeric',
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

    // Pengurus menyetujui pinjaman dan generate cicilan otomatis
    public function setujuiPinjamanDenganGenerateCicilan(Request $request, $id)
    {
        $request->validate([
            'tenor' => 'required|integer|min:1',
            'bunga' => 'required|numeric|min:0',
            'jumlah_cicilan_per_bulan' => 'required|numeric|min:1000',
        ]);

        DB::beginTransaction();

        try {
            $pinjaman = Pinjaman::findOrFail($id);

            if ($pinjaman->status !== 'menunggu') {
                return response()->json(['message' => 'Pinjaman sudah diproses sebelumnya'], 400);
            }

            // Update pinjaman
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

            // Generate cicilan otomatis per bulan
            for ($i = 1; $i <= $request->tenor; $i++) {
                Cicilan::create([
                    'pinjaman_id' => $pinjaman->id,
                    'bulan_ke' => $i,
                    'tanggal_jatuh_tempo' => Carbon::now()->addMonths($i)->toDateString(),
                    'jumlah_cicilan' => $request->jumlah_cicilan_per_bulan,
                    'status' => 'belum_lunas',
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pinjaman disetujui dan cicilan otomatis berhasil dibuat.',
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
