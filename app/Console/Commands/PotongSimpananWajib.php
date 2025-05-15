<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Simpanan;
use Carbon\Carbon;

class PotongSimpananWajib extends Command
{
    protected $signature = 'simpanan:potong-wajib';
    protected $description = 'Memotong simpanan wajib Rp100.000 untuk setiap anggota aktif setiap bulan';

    public function handle()
    {
        $tanggal = Carbon::now()->startOfMonth(); // Tanggal 1 bulan ini
        $jumlahSimpanan = 100000;

        $anggotaAktif = User::where('role', 'anggota')->where('status', 'aktif')->get();

        $berhasil = 0;
        $gagal = 0;

        foreach ($anggotaAktif as $anggota) {
            $sudahAda = Simpanan::where('user_id', $anggota->id)
                ->where('jenis', 'wajib')
                ->whereMonth('tanggal', $tanggal->month)
                ->whereYear('tanggal', $tanggal->year)
                ->exists();

            if ($sudahAda) {
                $this->info("Simpanan wajib untuk {$anggota->name} sudah ada.");
                $gagal++;
                continue;
            }

            Simpanan::create([
                'user_id' => $anggota->id,
                'jenis' => 'wajib',
                'jumlah' => $jumlahSimpanan,
                'tanggal' => $tanggal,
                'keterangan' => 'Potongan otomatis simpanan wajib bulan ' . $tanggal->translatedFormat('F Y'),
            ]);

            $this->info("Simpanan wajib untuk {$anggota->name} berhasil ditambahkan.");
            $berhasil++;
        }

        $this->info("Total berhasil: $berhasil, Total dilewati: $gagal");
    }
}
