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
        $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:users,nip',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'nomor_hp' => 'required|string|max:20',
            'unit_kerja' => 'required|string|max:100',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'nama' => $request->nama,
            'nip' => $request->nip,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'nomor_hp' => $request->nomor_hp,
            'unit_kerja' => $request->unit_kerja,
            'password' => Hash::make($request->password),
            'role' => 'anggota',
            'status' => 'menunggu', // default belum disetujui
            'sk_perjanjian_kerja' => null,     // belum ada dokumen
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

    public function uploadSK(Request $request)
{
    $request->validate([
            'nip' => 'required|exists:users,nip',
            'sk_perjanjian_kerja' => 'required|mimes:pdf|max:2048',
        ]);

        $user = User::where('nip', $request->nip)->first();

        if ($user->dokumen) {
            Storage::delete('public/sk/' . $user->dokumen);
        }

        $filename = uniqid() . '.' . $request->file->extension();
        $request->file->storeAs('public/sk', $filename);

        $user->update(['sk_perjanjian_kerja' => $filename]);

        return response()->json(['message' => 'Upload berhasil'], 200);
    }

     public function status($nip)
    {
        $user = User::where('nip', $nip)->first();

        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        return response()->json([
            'nama' => $user->nama,
            'status' => $user->status,
            'sk_perjanjian_kerja' => $user->dokumen ? 'Sudah Diupload' : 'Belum Diupload',
        ]);
    }
}
