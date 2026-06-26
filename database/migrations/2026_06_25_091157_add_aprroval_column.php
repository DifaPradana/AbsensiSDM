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
        Schema::table('izin_absens', function (Blueprint $table) {
            // Tambah kolom approval
            $table->foreignId('hrd_id')->nullable()->after('status')->constrained('users', 'user_id');
            $table->timestamp('hrd_at')->nullable()->after('hrd_id');
            $table->text('hrd_note')->nullable()->after('hrd_at');

            $table->foreignId('direktur_id')->nullable()->after('hrd_note')->constrained('users', 'user_id');
            $table->timestamp('direktur_at')->nullable()->after('direktur_id');
            $table->text('direktur_note')->nullable()->after('direktur_at');

            // Kolom status baru (sementara namanya beda)
            $table->enum('status_baru', [
                'menunggu_hrd',
                'menunggu_direktur',
                'disetujui',
                'ditolak_hrd',
                'ditolak_direktur',
            ])->default('menunggu_hrd')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_absens', function (Blueprint $table) {
            $table->dropForeign(['hrd_id']);
            $table->dropForeign(['direktur_id']);
            $table->dropColumn(['hrd_id', 'hrd_at', 'hrd_note', 'direktur_id', 'direktur_at', 'direktur_note', 'status_baru']);
        });
    }
};
