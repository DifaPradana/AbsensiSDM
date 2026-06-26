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
        DB::table('izin_absens')->update([
            'status_baru' => DB::raw("CASE status
            WHEN 'menunggu konfirmasi' THEN 'menunggu_hrd'
            WHEN 'disetujui'           THEN 'disetujui'
            WHEN 'ditolak'             THEN 'ditolak_hrd'
            ELSE 'menunggu_hrd'
        END")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('izin_absens')->update(['status_baru' => null]);
    }
};
