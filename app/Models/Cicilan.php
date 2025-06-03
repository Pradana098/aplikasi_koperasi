<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cicilan extends Model
{
    use HasFactory;

    protected $table = 'cicilan'; // pastikan nama tabel sesuai

    protected $fillable = [
        'pinjaman_id',
        'bulan_ke',
        'tanggal_jatuh_tempo',
        'jumlah_cicilan',
        'status',
    ];

    // Relasi ke Pinjaman
    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class);
    }
}
