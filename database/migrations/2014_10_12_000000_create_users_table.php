<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('no_telepon')->unique();
        $table->string('password');
        $table->string('nip')->nullable();
        $table->string('tempat_lahir')->nullable();
        $table->date('tanggal_lahir')->nullable();
        $table->text('alamat_rumah')->nullable();
        $table->string('unit_kerja')->nullable();
        $table->string('sk_perjanjian_kerja')->nullable();
        $table->string('photo_path')->nullable();
        $table->enum('role', ['anggota', 'pengawas', 'pengurus'])->default('anggota');
        $table->enum('status', ['menunggu', 'aktif', 'ditolak'])->default('menunggu');
        $table->rememberToken();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
