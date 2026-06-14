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
        Schema::create('izin_absens', function (Blueprint $table) {
            $table->id('izin_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->enum('tipe_izin', ['sakit', 'izin', 'cuti'])->default('izin');
            $table->date('mulai_izin');
            $table->string('dokumen_izin')->nullable();
            $table->date('akhir_izin')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_absens');
    }
};
