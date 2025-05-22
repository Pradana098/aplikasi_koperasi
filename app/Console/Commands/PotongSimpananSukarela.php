<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Simpanan;
use Carbon\Carbon;

class PotongSimpananSukarela extends Command
{
    protected $signature = 'potong:simpanan-sukarela';
    protected $description = 'Mencatat potongan simpanan sukarela bulanan untuk semua anggota yang mengaktifkan';

    public function handle()
    {
        $tanggal = Carbon::now()->startOfMonth();

        $anggotaList = User::where('role', 'anggota')
            ->whereNotNull('auto_sukarela')
            ->get();

        foreach ($anggotaList as $anggota) {
            // Cek apakah sudah dipotong bulan ini
            $sudah = Simpanan::where('user_id', $anggota->id)
                ->where('jenis', 'sukarela')
                ->whereMonth('tanggal', $tanggal->month)
                ->whereYear('tanggal', $tanggal->year)
                ->exists();

            if ($sudah) {
                continue;
            }

            Simpanan::create([
                'user_id' => $anggota->id,
                'jenis' => 'sukarela',
                'jumlah' => $anggota->auto_sukarela,
                'tanggal' => $tanggal,
                'keterangan' => 'Potongan otomatis bulanan simpanan sukarela',
            ]);
        }

        $this->info('Potongan sukarela bulanan berhasil dijalankan.');
    }
}

