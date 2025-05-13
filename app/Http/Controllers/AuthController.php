<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100',
            'no_telepon' => 'required|string|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'nip' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'alamat_rumah' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'sk_perjanjian_kerja' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Upload file SK Perjanjian Kerja
        $skFile = $request->file('sk_perjanjian_kerja');
        $skFilename = Str::uuid() . '.' . $skFile->getClientOriginalExtension();
        $skFile->storeAs('public/sk_perjanjian_kerja', $skFilename);

        // Buat akun anggota
        $user = User::create([
            'nama' => $request->nama,
            'no_telepon' => $request->no_telepon,
            'password' => Hash::make($request->password),
            'nip' => $request->nip,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat_rumah' => $request->alamat_rumah,
            'unit_kerja' => $request->unit_kerja,
            'sk_perjanjian_kerja' => $skFilename,
            'role' => 'anggota',
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pendaftaran berhasil, menunggu verifikasi pengurus.',
            'data' => $user
        ]);
    }
    public function login(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);


        $user = User::where('nip', $request->nip)->first();

        if (!$user) {
            return response()->json(['message' => 'NIP tidak ditemukan.'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password salah.'], 401);
        }

        if ($user->status !== 'aktif') {
            return response()->json([
                'message' => 'Akun belum aktif.',
                'status_user' => $user->status,
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

}
