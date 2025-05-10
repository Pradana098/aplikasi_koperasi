<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use App\Notifications\NotifikasiHelper;

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

    public function listPendingAnggota()
    {
        $anggota = User::where('role', 'anggota')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $anggota
        ]);
    }

    public function approveAnggota($id)
    {
        try {
            $anggota = User::where('id', $id)->where('role', 'anggota')->firstOrFail();
            $anggota->status = 'aktif';
            $anggota->save();

            return response()->json([
                'status' => true,
                'message' => 'Anggota berhasil disetujui.',
                'data' => $anggota
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Anggota tidak ditemukan.'
            ], 404);
        }
    }

    public function rejectAnggota($id)
    {
        try {
            $anggota = User::where('id', $id)->where('role', 'anggota')->firstOrFail();
            $anggota->status = 'ditolak';
            $anggota->save();

            return response()->json([
                'status' => true,
                'message' => 'Anggota berhasil ditolak.',
                'data' => $anggota
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Anggota tidak ditemukan.'
            ], 404);
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

    public function detailStatusPendaftaran($id)
    {
        try {
            $anggota = User::where('id', $id)->where('role', 'anggota')->firstOrFail();

            $message = match ($anggota->status) {
                'aktif' => 'Pendaftaran Berhasil Diterima',
                'ditolak' => 'Pendaftaran Ditolak',
                'pending' => 'Menunggu Persetujuan',
                default => 'Status tidak diketahui',
            };

            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $anggota->id,
                    'nama' => $anggota->nama,
                    'status' => $anggota->status,
                    'message' => $message,
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Anggota tidak ditemukan.'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nip' => 'required|unique:users,nip',
            'telepon' => 'required|string',
        ]);


        $token = Str::uuid();

        $user = User::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nip' => $validated['nip'],
            'telepon' => $validated['telepon'],
            'role' => 'anggota',
            'status' => 'aktif',
            'password_token' => $token,
            'password' => null,
        ]);


        NotifikasiHelper::kirimLinkPassword($user->telepon, $token);

        return response()->json(['message' => 'Anggota berhasil ditambahkan dan notifikasi dikirim.'], 201);
    }
}
