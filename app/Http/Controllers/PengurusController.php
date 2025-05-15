<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Anggota;
use Illuminate\Support\Str;
use App\Notifications\NotifikasiHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Simpanan;
use Carbon\Carbon;

class PengurusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:pengurus']);
    }

    public function index()
    {
        return response()->json([
            'message' => 'Selamat datang di dashboard pengurus'
        ]);
    }


    public function getAnggotaByStatus($status)
    {
        try {
            if (!in_array($status, ['pending', 'aktif', 'ditolak'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status tidak valid. Status harus berupa: pending, aktif, atau ditolak'
                ], 400);
            }

            $anggota = User::where('role', 'anggota')
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Data anggota berhasil diambil',
                'data' => $anggota
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error mengambil data anggota: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data anggota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:aktif,ditolak',
        ]);

        $anggota = User::find($id);

        if (!$anggota || $anggota->role !== 'anggota') {
            return response()->json(['error' => 'Anggota tidak ditemukan'], 404);
        }

        DB::beginTransaction();
        try {
            $anggota->status = $request->status;
            $anggota->save();

            // Jika status disetujui (aktif), lakukan pemotongan gaji untuk simpanan pokok
            if ($request->status === 'aktif') {
                $jumlahSimpananPokok = 100000;

                if ($anggota->gaji < $jumlahSimpananPokok) {
                    throw new \Exception('Gaji anggota tidak mencukupi untuk simpanan pokok.');
                }


                // Simpan data ke tabel simpanan
                Simpanan::create([
                    'user_id' => $anggota->id,
                    'jenis' => 'pokok',
                    'jumlah' => $jumlahSimpananPokok,
                    'tanggal' => Carbon::now(),
                    'keterangan' => 'Pemotongan otomatis dari gaji saat disetujui'
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Status berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memverifikasi: ' . $e->getMessage()], 500);
        }
    }
    public function jumlahAnggota()
    {
        $jumlah = User::where('role', 'anggota')->count();
        return response()->json([
            'status' => true,
            'total_anggota' => $jumlah
        ]);
    }

    public function riwayatWajibByPengurus(Request $request, $user_id)
    {
        $user = $request->user();

        // Cek role harus pengurus
        if ($user->role !== 'pengurus') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pengurus yang bisa mengakses data ini.'
            ], 403);
        }

        $anggota = \App\Models\User::where('id', $user_id)->where('role', 'anggota')->first();
        if (!$anggota) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anggota tidak ditemukan.'
            ], 404);
        }

        $riwayat = \App\Models\Simpanan::where('user_id', $user_id)
            ->where('jenis', 'wajib')
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'anggota' => $anggota->only(['id', 'name', 'email']),
                'riwayat_simpanan_wajib' => $riwayat
            ],
        ]);
    }
    public function listNotifikasi(Request $request)
{
    $user = $request->user();
    if ($user->role !== 'pengurus') {
        return response()->json([
            'status' => 'error',
            'message' => 'Hanya pengurus yang bisa melihat notifikasi.'
        ], 403);
    }

    $notifikasi = \App\Models\Notifikasi::orderBy('created_at', 'desc')->get();

    return response()->json([
        'status' => 'success',
        'data' => $notifikasi,
    ]);
}



}
