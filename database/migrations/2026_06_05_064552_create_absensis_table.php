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
        Schema::create('absensis', function (Blueprint $table) {
            $table->id('absensi_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->enum('tipe_absensi', ['hadir', 'sakit', 'izin', 'alpha'])->default('hadir');
            $table->string('photo_masuk')->nullable();
            $table->string('photo_pulang')->nullable();
            $table->dateTime('waktu_absen_masuk')->nullable();
            $table->dateTime('waktu_absen_pulang')->nullable();
            $table->double('latitude_masuk', 10, 7); // 10 adalah total digit, dan 7 adalah total digit belakang koma
            $table->double('longitude_masuk', 10, 7);
            $table->double('latitude_pulang', 10, 7)->nullable();
            $table->double('longitude_pulang', 10, 7)->nullable();
            $table->string('status_absensi_masuk');
            $table->string('status_absensi_pulang')->nullable();
            $table->string('lokasi_masuk');
            $table->string('lokasi_pulang')->nullable();
            $table->string('note_masuk')->nullable();
            $table->string('note_pulang')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
