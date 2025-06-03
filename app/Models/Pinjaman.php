<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    use HasFactory;

    protected $table = 'pinjaman'; // nama tabel sesuai dengan database kamu

    protected $fillable = [
        'user_id',
        'jumlah_pinjaman',
        'tenor',
        'bunga',
        'tanggal_pengajuan',
        'status',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Cicilan (satu pinjaman punya banyak cicilan)
    public function cicilan()
    {
        return $this->hasMany(Cicilan::class);
    }
}
