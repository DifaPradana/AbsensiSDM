<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('lokasi_absensis', function (Blueprint $table) {
            $table->id('lokasi_id');
            $table->string('nama_lokasi');
            $table->decimal('latitude_lokasi', 10, 7); // 10 adalah total digit, dan 7 adalah total digit belakang koma
            $table->decimal('longitude_lokasi', 10, 7);
            $table->integer('radius_meter')->default(50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_absensis');
    }
};
