<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteDailyReport extends Command
{
    protected $signature = 'app:delete-daily-report {--dry-run}';
    protected $description = 'Hapus dokumen daily report bulan lalu & record DB-nya';

    public function handle(): int
    {
        // Rentang bulan lalu (misal dijalankan tgl 5 Juli -> target Juni)
        $start = now()->subMonthNoOverflow()->startOfMonth();
        $end   = now()->subMonthNoOverflow()->endOfMonth();

        $dryRun = $this->option('dry-run');

        $this->info("Target hapus daily report periode: {$start->toDateString()} s/d {$end->toDateString()}");

        $records = DailyReport::whereBetween('created_at', [$start, $end])
            ->whereNotNull('path_dokumen')
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada daily report yang perlu dihapus.');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($records as $record) {
            $path = $record->path_dokumen;

            if ($dryRun) {
                $this->line("  [dry-run] akan hapus: {$path}");
                continue;
            }

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            $record->delete();
            $deleted++;
        }

        $dryRun
            ? $this->warn('Dry-run selesai. Tidak ada yang dihapus.')
            : $this->info("✅ {$deleted} daily report berhasil dihapus.");

        return self::SUCCESS;
    }
}
