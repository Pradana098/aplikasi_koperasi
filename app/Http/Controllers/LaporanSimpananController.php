<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanSimpananController extends Controller
{
   public function index(Request $request)
{

    $user = $request->user();
    if (!in_array($user->role, ['pengurus', 'pengawas'])) {
        return response()->json([
            'message' => 'Akses ditolak. Halaman ini hanya untuk pengurus dan pengawas.'
        ], 403);
    }

      $request->validate([
        'start' => 'required|date',
        'end' => 'required|date|after_or_equal:start',
    ]);

    $start = $request->query('start');
    $end = $request->query('end');

    $laporan = DB::table('users') // dari tabel users
        ->select(
            'users.id',
            'users.nama',
            DB::raw("COALESCE(SUM(CASE WHEN simpanan.jenis = 'pokok' THEN simpanan.jumlah ELSE 0 END), 0) as simpanan_pokok"),
            DB::raw("COALESCE(SUM(CASE WHEN simpanan.jenis = 'wajib' THEN simpanan.jumlah ELSE 0 END), 0) as simpanan_wajib")
        )
        ->leftJoin('simpanan', function($join) use ($start, $end) {
            $join->on('simpanan.user_id', '=', 'users.id')
                ->whereBetween('simpanan.tanggal', [$start, $end])
                ->whereIn('simpanan.jenis', ['pokok', 'wajib']);
        })
        ->where('users.role', 'anggota') // hanya tampilkan anggota
        ->groupBy('users.id', 'users.nama')
        ->get();

    return response()->json([
        'periode' => "$start sampai $end",
        'data' => $laporan,
    ]);
}
}
