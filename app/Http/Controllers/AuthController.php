<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:255',
        'no_telepon' => 'required|string|max:15|unique:users,no_telepon',
        'password' => 'required|string|min:6|confirmed',
        'nip' => 'required|string|max:50',
        'tempat_lahir' => 'nullable|string|max:100',
        'tanggal_lahir' => 'nullable|date',
        'alamat_rumah' => 'nullable|string',
        'unit_kerja' => 'nullable|string',
        'sk_perjanjian_kerja' => 'nullable|string',
    ]);

    $user = User::create([
        'nama' => $request->nama,
        'no_telepon' => $request->no_telepon,
        'password' => Hash::make($request->password),
        'nip' => $request->nip,
        'tempat_lahir' => $request->tempat_lahir,
        'tanggal_lahir' => $request->tanggal_lahir,
        'alamat_rumah' => $request->alamat_rumah,
        'unit_kerja' => $request->unit_kerja,
        'sk_perjanjian_kerja' => $request->sk_perjanjian_kerja,
        'role' => 'anggota',
        'status' => 'menunggu', // Belum diverifikasi
    ]);

    return response()->json([
        'message' => 'Pendaftaran berhasil, menunggu verifikasi pengawas',
        'user_id' => $user->id,
    ], 201);
}

    public function login(Request $request)
    {
        $request->validate([
            'no_telepon' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('no_telepon', $request->no_telepon)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Nomor telepon atau password salah'
            ], 401);
        }

        if ($user->status != 'aktif') {
            return response()->json([
                'message' => 'Akun Anda belum diverifikasi oleh pengurus.'
            ], 403);
        }

        $token = $user->createToken('token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'role' => $user->role,
                'status' => $user->status,
            ]
        ]);
    }
}
