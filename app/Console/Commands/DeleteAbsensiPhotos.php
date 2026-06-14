<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteAbsensiPhotos extends Command
{
    protected $signature = 'absensi:delete-photos {--dry-run}';
    protected $description = 'Hapus foto absensi bulan lalu & nullify kolom DB';

    public function handle(): int
    {
        $cutoff  = now()->startOfMonth(); // awal bulan ini = batas bawah "bulan lalu"
        $dryRun  = $this->option('dry-run');

        $records = Absensi::where('created_at', '<', $cutoff)
            ->where('tipe_absensi', 'hadir') // hanya hadir
            ->where(
                fn($q) => $q
                    ->whereNotNull('photo_masuk')
                    ->orWhereNotNull('photo_pulang')
            )
            ->get();

        if ($records->isEmpty()) {
            $this->info('Tidak ada foto yang perlu dihapus.');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($records as $record) {
            foreach (['photo_masuk', 'photo_pulang'] as $field) {
                $path = $record->$field;
                if (!$path) continue;

                if ($dryRun) {
                    $this->line("  [dry-run] akan hapus: {$path}");
                    continue;
                }

                Storage::disk('public')->delete($path);
                $record->$field = null;
                $deleted++;
            }

            if (!$dryRun) {
                $record->save();
            }
        }

        $dryRun
            ? $this->warn("Dry-run selesai. Tidak ada yang dihapus.")
            : $this->info("✅ {$deleted} foto berhasil dihapus.");

        return self::SUCCESS;
    }
}
