<?php

namespace App\Console\Commands;

use App\Models\ExportDailyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteOldExportDailyReport extends Command
{
    /**
     * Execute the console command.
     */
    protected $signature = 'export:delete-old-daily-report {--days=7 : Hapus file lebih dari N hari} {--dry-run : Simulasi tanpa benar-benar menghapus}';

    protected $description = 'Hapus file export daily report yang sudah lama';

    public function handle(): void
    {
        $days   = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $exports = ExportDailyReport::where('created_at', '<', now()->subDays($days))->get();

        if ($exports->isEmpty()) {
            $this->info('Tidak ada file export yang perlu dihapus.');
            return;
        }

        if ($dryRun) {
            $this->warn("[DRY RUN] Ditemukan {$exports->count()} file export yang akan dihapus:");
        }

        $deleted = 0;

        foreach ($exports as $export) {
            if ($dryRun) {
                $this->line("- {$export->filename} (dibuat: {$export->created_at})");
                continue;
            }

            if (Storage::disk('public')->exists($export->path)) {
                Storage::disk('public')->delete($export->path);
            }
            // Hapus record DB
            $export->delete();
            $deleted++;
        }

        if ($dryRun) {
            $this->info("[DRY RUN] Total {$exports->count()} file export yang akan dihapus (tidak ada yang benar-benar dihapus).");
            return;
        }

        $this->info("Berhasil menghapus {$deleted} file export daily report.");
    }
}
