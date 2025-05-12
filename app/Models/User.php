<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPasswordTrait;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'nama',
        'no_telepon',
        'password',
        'nip',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat_rumah',
        'unit_kerja',
        'sk_perjanjian_kerja',
        'role',
        'status',
        'photo_path'
    ];

    /**
     * Hidden attributes.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the role of the user.
     */
    public function isRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Custom password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordCustom($token));
    }

    /**
     * Relasi ke tabel Simpanan
     */
    public function simpanans()
    {
        return $this->hasMany(Simpanan::class);
    }

    /**
     * Relasi ke tabel Pinjaman
     */
    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class);
    }
}
