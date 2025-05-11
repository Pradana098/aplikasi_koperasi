<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalonAnggota extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik', 'nama', 'password', 'no_hp', 'status',
    ];

    protected $hidden = [
        'password',
    ];
}
