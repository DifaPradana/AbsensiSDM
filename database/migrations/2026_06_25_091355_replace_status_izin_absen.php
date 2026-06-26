<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('izin_absens', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('izin_absens', function (Blueprint $table) {
            // Rename status_baru → status
            $table->renameColumn('status_baru', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_absens', function (Blueprint $table) {
            $table->renameColumn('status', 'status_baru');
        });

        Schema::table('izin_absens', function (Blueprint $table) {
            $table->enum('status', ['menunggu konfirmasi', 'disetujui', 'ditolak'])
                ->default('menunggu konfirmasi')
                ->after('status_baru');
        });

        DB::table('izin_absens')->update([
            'status' => DB::raw("CASE status_baru
            WHEN 'menunggu_hrd'       THEN 'menunggu konfirmasi'
            WHEN 'menunggu_direktur'  THEN 'menunggu konfirmasi'
            WHEN 'disetujui'          THEN 'disetujui'
            ELSE 'ditolak'
        END")
        ]);

        Schema::table('izin_absens', function (Blueprint $table) {
            $table->dropColumn('status_baru');
        });
    }
};
