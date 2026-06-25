<?php

namespace App\Console\Commands;

use App\Models\ExportAbsen;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldExportAbsensi extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'export:delete-old-absensi {--days=1 : Hapus file lebih dari N hari}';

    protected $description = 'Hapus file export absensi yang sudah lama';

    public function handle(): void
    {
        $days = (int) $this->option('days');

        $exports = ExportAbsen::where('created_at', '<', now()->subDays($days))->get();

        if ($exports->isEmpty()) {
            $this->info('Tidak ada file export yang perlu dihapus.');
            return;
        }

        $deleted = 0;

        foreach ($exports as $export) {
            if (Storage::disk('public')->exists($export->path)) {
                Storage::disk('public')->delete($export->path);
            }
            // Hapus record DB
            $export->delete();
            $deleted++;
        }

        $this->info("Berhasil menghapus {$deleted} file export absensi.");
    }
}
