<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TransferSaldoController extends Controller
{
    // Pengurus melakukan transfer saldo ke anggota (manual)
    public function transferSaldo($id)
    {
        DB::beginTransaction();
        try {
            $pinjaman = Pinjaman::findOrFail($id);

            if ($pinjaman->status !== 'disetujui') {
                return response()->json(['message' => 'Pinjaman belum disetujui atau sudah diproses'], 400);
            }

            // Transfer saldo ke anggota
            $user = User::findOrFail($pinjaman->user_id);
            $user->saldo += $pinjaman->jumlah_pinjaman;
            $user->save();

            // Update status pinjaman menjadi 'dana_dicairkan'
            $pinjaman->status = 'dana_dicairkan';
            $pinjaman->save();

            DB::commit();

            return response()->json(['message' => 'Saldo berhasil ditransfer ke anggota']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Gagal melakukan transfer saldo', 'error' => $e->getMessage()], 500);
        }
    }
}
