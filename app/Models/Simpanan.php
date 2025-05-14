<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simpanan extends Model
{
    use HasFactory;

    // Menentukan nama tabel yang digunakan
    protected $table = 'simpanan';

    // Relasi ke jenis_simpanan
    public function jenisSimpanan()
    {
        return $this->belongsTo(\App\Models\JenisSimpanan::class, 'jenis_simpanan_id');
    }
}
