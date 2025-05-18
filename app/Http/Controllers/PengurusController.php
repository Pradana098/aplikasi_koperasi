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
use App\Models\Notifikasi;

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
            $jumlahPokok = 40000;
            $jumlahWajib = 50000;

            Simpanan::create([
                'user_id' => $anggota->id,
                'jenis' => 'pokok',
                'jumlah' => $jumlahPokok,
                'tanggal' => Carbon::now(),
                'keterangan' => 'Potongan otomatis simpanan pokok saat persetujuan anggota'
            ]);

            Simpanan::create([
                'user_id' => $anggota->id,
                'jenis' => 'wajib',
                'jumlah' => $jumlahWajib,
                'tanggal' => Carbon::now(),
                'keterangan' => 'Potongan otomatis simpanan wajib saat persetujuan anggota'
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
    public function semuaRiwayatSimpanan(Request $request)
    {
        $user = $request->user();

        // Hanya pengurus yang boleh mengakses
        if ($user->role !== 'pengurus') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya pengurus yang dapat melihat semua riwayat simpanan.'
            ], 403);
        }

        $riwayat = Simpanan::with('anggota') // pastikan relasi 'anggota' dibuat
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $riwayat
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

        $notifikasi = Notifikasi::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifikasi,
        ]);
    }



}
