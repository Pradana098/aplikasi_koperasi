<?php

namespace App\Http\Controllers;

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
            'nip' => 'required|string|max:50|unique:users,nip',
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
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Pendaftaran berhasil. Menunggu persetujuan pengurus.',
            'user_id' => $user->id,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('nip', $request->nip)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'NIP atau password salah.'], 401);
        }

        if ($user->status !== 'aktif') {
            return response()->json([
                'message' => 'Akun belum aktif. Status Anda saat ini: ' . $user->status
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'role' => $user->role,
                'status' => $user->status,
                'nip' => $user->nip,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
