<?php

namespace App\Http\Controllers;

use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use App\Models\Simpanan;
use App\Models\SimpananWajib;
use Carbon\Carbon;

class AnggotaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:anggota']);
    }

    public function index()
    {
        return response()->json([
            'message' => 'Selamat datang di dashboard anggota',
        ]);
    }

    public function statusPendaftaranSaya(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak.'
            ], 403);
        }

        $message = match ($user->status) {
            'aktif' => 'Pendaftaran Berhasil Diterima',
            'ditolak' => 'Pendaftaran Ditolak',
            'pending' => 'Menunggu Persetujuan',
            default => 'Status tidak diketahui',
        };

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'status' => $user->status,
                'message' => $message,
            ]
        ]);
    }

    public function riwayatWajib(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya anggota yang dapat melihat riwayat simpanan wajib.'
            ], 403);
        }

        $riwayat = Simpanan::where('user_id', $user->id)
            ->where('jenis', 'wajib')
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
        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya anggota yang bisa melihat notifikasi.'
            ], 403);
        }

        $notifikasi = \App\Models\Notifikasi::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifikasi,
        ]);

    }

    public function SimpananWajib(Request $request)
    {


        $user = $request->user();
        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
            ], 403);
        }

        $simpanan = Simpanan::where('user_id', $user->id)
            ->where('jenis', 'wajib')
            ->orderByDesc('tanggal')
            ->get();


        $total = $simpanan->sum('jumlah');
        $lastDate = optional($simpanan->first())->tanggal;
        $statusBulanIni = $simpanan->where('tanggal', '>=', now()->startOfMonth())->count() > 0;

        return response()->json([
            'total' => $total,
            'last_cut_date' => $lastDate ? Carbon::parse($lastDate)->translatedFormat('d F Y') : null,
            'status_bulan_ini' => $statusBulanIni,
            'jadwal_pemotongan' => 'Setiap tanggal 1 tiap bulan',
            'riwayat' => $simpanan->map(function ($item) {
                return [
                    'bulan' => Carbon::parse($item->tanggal)->translatedFormat('F Y'),
                    'jumlah' => $item->jumlah,
                    'status' => '✅',
                ];
            }),
        ]);
    }

    public function aturPotonganSukarela(Request $request)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:1000',
        ]);

        $user = $request->user();
        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya anggota yang dapat mengatur potongan sukarela.',

            ], 403);
        }


        $user->auto_sukarela = $request->jumlah;
        $user->save();

        Simpanan::create([
            'user_id' => $user->id,
            'jenis' => 'sukarela',
            'jumlah' => $request->jumlah,
            'tanggal' => Carbon::now(),
            'keterangan' => 'Potongan awal simpanan sukarela',
        ]);

        return response()->json([
            'message' => 'Potongan sukarela otomatis berhasil diatur dan langsung dipotong hari ini.',
        ]);
    }
    public function berhentiSimpananSukarela(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'anggota') {
            return response()->json([
                'message' => 'Hanya anggota yang dapat mengubah simpanan sukarela.'
            ], 403);
        }

        $user->auto_sukarela = null;
        $user->save();

        return response()->json([
            'message' => 'Simpanan sukarela otomatis berhasil dihentikan.'
        ]);
    }


    public function statusPotonganSukarela(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'anggota') {
            return response()->json([
                'status' => 'error',
            ], 403);
        }


        return response()->json([
            'auto_sukarela' => $user->auto_sukarela,
            'status' => $user->auto_sukarela ? 'aktif' : 'tidak aktif',
            'pesan' => $user->auto_sukarela
                ? 'Potongan otomatis aktif sebesar Rp ' . number_format($user->auto_sukarela, 0, ',', '.')
                : 'Potongan otomatis belum diaktifkan',
        ]);
    }
}

