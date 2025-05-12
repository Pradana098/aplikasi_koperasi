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
        Schema::create('cicilan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pinjaman_id');
            $table->foreign('pinjaman_id')->references('id')->on('pinjaman')->onDelete('cascade');
            $table->integer('bulan_ke');
            $table->date('tanggal_jatuh_tempo');
            $table->decimal('jumlah_cicilan', 15, 2);
            $table->enum('status', ['belum_lunas', 'lunas'])->default('belum_lunas');
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
        Schema::dropIfExists('cicilans');
    }
};
