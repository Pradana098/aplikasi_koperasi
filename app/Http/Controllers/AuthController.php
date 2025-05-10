<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20|unique:users,no_telepon',
            'password' => 'required|string|min:6|confirmed',
            'nip' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'alamat_rumah' => 'required|string',
            'unit_kerja' => 'required|string',
            'sk_perjanjian_kerja' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Simpan file
        $skFile = $request->file('sk_perjanjian_kerja');
        $filename = time() . '_' . Str::random(10) . '.' . $skFile->getClientOriginalExtension();
        $skPath = $skFile->storeAs('sk_perjanjian_kerja', $filename, 'public');

        // Simpan user
        $user = User::create([
            'nama' => $request->nama, // <-- pastikan field ini ada
            'no_telepon' => $request->no_telepon,
            'password' => bcrypt($request->password),
            'nip' => $request->nip,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat_rumah' => $request->alamat_rumah,
            'unit_kerja' => $request->unit_kerja,
            'sk_perjanjian_kerja' => $skPath,
            'role' => 'anggota',
            'status' => 'menunggu',
        ]);
        return response()->json([
            'message' => 'Pendaftaran berhasil. Menunggu persetujuan pengurus.',
            'user' => $user
        ], 201);
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

public function submitPassword(Request $request, $token)
{
    $user = User::where('password_token', $token)->firstOrFail();

    $request->validate([
        'password' => 'required|min:6|confirmed',
    ]);

    $user->update([
        'password' => bcrypt($request->password),
        'password_token' => null,
    ]);

    return response()->json(['message' => 'Password berhasil dibuat']);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }
}
