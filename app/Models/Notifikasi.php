<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
     protected $table = 'notifikasi';
    protected $fillable = ['judul', 'pesan', 'is_read'];
}
