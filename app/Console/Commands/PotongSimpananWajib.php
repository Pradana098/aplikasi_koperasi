<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Simpanan;
use App\Models\User;
use App\Models\Notifikasi;
use Carbon\Carbon;

class PotongSimpananWajib extends Command
{
    protected $signature = 'simpanan:potong-wajib';
    protected $description = 'Memotong simpanan wajib anggota setiap bulan';

    public function handle()
    {
        $anggota = User::where('role', 'anggota')->get();
        $tanggal = Carbon::now()->startOfMonth();
        $jumlahPotongan = 100000;

        $berhasil = 0;
        $dilewati = 0;

        foreach ($anggota as $user) {
            $punyaPokok = Simpanan::where('user_id', $user->id)
                ->where('jenis', 'pokok')
                ->exists();

            if (!$punyaPokok) {
                $dilewati++;
                continue;
            }

            Simpanan::create([
                'user_id' => $user->id,
                'jenis' => 'wajib',
                'jumlah' => $jumlahPotongan,
                'tanggal' => $tanggal,
                'keterangan' => 'Potongan otomatis simpanan wajib bulan ' . $tanggal->format('F Y'),
            ]);

            $berhasil++;
        }

        Notifikasi::create([
            'judul' => 'Potongan Simpanan Wajib Bulanan',
            'pesan' => "Berhasil potong simpanan wajib untuk $berhasil anggota. Dilewati $dilewati anggota yang belum punya simpanan pokok.",
            'is_read' => false,
        ]);

        $this->info("✅ Potongan selesai. $berhasil berhasil, $dilewati dilewati.");
    }
}
