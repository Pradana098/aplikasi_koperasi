<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        $user = Auth::user();

        if (!$user || $user->role !== $role) {
            return response()->json(['message' => 'Unauthorized (Role)'], 403);
        }
        if ($role === 'anggota') {
            // // Cek apakah sudah bayar simpanan pokok
            // $sudahBayarPokok = $user->simpanan()
            //     ->where('jenis', 'pokok')
            //     ->exists();
                
            // if (!$sudahBayarPokok) {
            //     return response()->json([
            //         'message' => 'Silakan lakukan pembayaran simpanan pokok terlebih dahulu.',
            //         'status' => 'require_simpanan_pokok'
            //     ], 403);
            // }
        }

        return $next($request);
    }
}

