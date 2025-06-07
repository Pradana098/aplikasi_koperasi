<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis'; // sesuaikan dengan nama tabel

    protected $fillable = [
        'anggota_id',
        'tipe',
        'deskripsi',
        'jumlah',
        'tanggal',
    ];

    public function anggota()
    {
        return $this->belongsTo(User::class, 'anggota_id');
    }
}
