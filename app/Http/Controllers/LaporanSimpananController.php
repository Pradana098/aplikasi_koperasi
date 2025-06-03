<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanSimpananController extends Controller
{
    public function LaporanSimpanan(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['pengurus', 'pengawas'])) {
            return response()->json([
                'message' => 'Akses ditolak. Halaman ini hanya untuk pengurus dan pengawas.'
            ], 403);
        }

        // Ambil bulan & tahun dari request atau gunakan sekarang
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun');

        if (!$bulan || !$tahun) {
            $now = Carbon::now();
            $bulan = $now->month;
            $tahun = $now->year;
        } else {
            $now = Carbon::createFromDate($tahun, $bulan, 1);
        }

        // Query simpanan
        $laporan = DB::table('users')
            ->select(
                'users.id',
                'users.nama',
                DB::raw("COALESCE(SUM(CASE WHEN simpanan.jenis = 'pokok' THEN simpanan.jumlah ELSE 0 END), 0) as simpanan_pokok"),
                DB::raw("COALESCE(SUM(CASE WHEN simpanan.jenis = 'wajib' THEN simpanan.jumlah ELSE 0 END), 0) as simpanan_wajib"),
                DB::raw("COALESCE(SUM(CASE WHEN simpanan.jenis = 'sukarela' THEN simpanan.jumlah ELSE 0 END), 0) as simpanan_sukarela")
            )
            ->leftJoin('simpanan', function ($join) use ($bulan, $tahun) {
                $join->on('simpanan.user_id', '=', 'users.id')
                    ->whereMonth('simpanan.tanggal', $bulan)
                    ->whereYear('simpanan.tanggal', $tahun)
                    ->whereIn('simpanan.jenis', ['pokok', 'wajib', 'sukarela']);
            })
            ->where('users.role', 'anggota')
            ->groupBy('users.id', 'users.nama')
            ->get();

        // Filter hanya yang punya simpanan (jika mau ditampilkan sebagian)
        $laporanBulanIni = $laporan->filter(function ($item) {
            return $item->simpanan_pokok > 0 || $item->simpanan_wajib > 0;
        })->values();

        if ($laporanBulanIni->isEmpty()) {
            return response()->json([
                'periode' => $now->translatedFormat('F Y'),
                'message' => 'Tidak ada data simpanan bulan ini',
                'data' => []
            ]);
        }

        return response()->json([
            'periode' => $now->translatedFormat('F Y'),
            'data' => $laporanBulanIni,
        ]);
    }
}
