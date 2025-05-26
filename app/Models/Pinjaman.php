<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    use HasFactory;

    protected $table = 'pinjaman'; // jika nama tabel tidak default 'pinjamen'

    protected $fillable = [
        'user_id',
        'jumlah_pinjaman',
        'tenor',
        'bunga',          // kalau di insert juga
        'tanggal_pengajuan', // kalau di insert juga
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
