<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Simpanan;
use App\Models\JenisSimpanan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'nama' => 'Siti Pengawas',
            'nip' => '1234567891',
            'no_telepon' => '089876543210',
            'password' => Hash::make('pengawas123'),
            'role' => 'pengawas',
            'status' => 'aktif',
        ]);

        User::create([
            'nama' => 'Budi Pengurus',
            'nip' => '1234567890',
            'no_telepon' => '089876543211',
            'password' => Hash::make('pengurus123'),
            'role' => 'pengurus',
            'status' => 'aktif',
        ]);



        User::create([
            'nama' => 'Ani Anggota',
            'nip' => '0987654321',
            'no_telepon' => '089876543212',
            'password' => Hash::make('anggota123'),
            'role' => 'anggota',
            'status' => 'aktif',
        ]);

           User::create([
            'nama' => 'pani Anggota',
            'nip' => '0987654322',
            'no_telepon' => '089876543213',
            'password' => Hash::make('anggota123'),
            'role' => 'anggota',
            'status' => 'pending',
        ]);

   User::create([
            'nama' => 'ali Anggota',
            'nip' => '0987654323',
            'no_telepon' => '089876543214',
            'password' => Hash::make('anggota123'),
            'role' => 'anggota',
            'status' => 'ditolak',
        ]);

           User::create([
            'nama' => 'alan Anggota',
            'nip' => '0987654324',
            'no_telepon' => '089876543215',
            'password' => Hash::make('anggota123'),
            'role' => 'anggota',
            'status' => 'aktif',
        ]);

          $user = User::where('nama', 'Ani Anggota')->first();

          Simpanan::create([
            'user_id' => $user->id,
            'jenis' => 'pokok', 
            'jumlah' => 100000, 
            'tanggal' => now(),
        ]);

    }
}
