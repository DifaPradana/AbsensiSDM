<?php

namespace App\Jobs;

use App\Models\DailyReport;
use App\Models\ExportDailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ZipArchive;

class ExportDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ?string $tanggalAwal,
        protected ?string $tanggalAkhir,
    ) {}

    public function handle(): void
    {
        $filename    = 'daily_report_' . now()->format('Ymd_His') . '.zip';
        $storagePath = 'exports/' . $filename;
        $fullPath    = storage_path('app/public/' . $storagePath);

        if (! file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $reports = DailyReport::with('user')
            ->whereHas('user')
            ->when($this->tanggalAwal,  fn($q) => $q->whereDate('created_at', '>=', $this->tanggalAwal))
            ->when($this->tanggalAkhir, fn($q) => $q->whereDate('created_at', '<=', $this->tanggalAkhir))
            ->latest()
            ->get();

        $zip       = new ZipArchive();
        $totalRows = 0;

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($reports as $report) {
                $filePath = storage_path('app/public/' . $report->path_dokumen);

                if (! file_exists($filePath)) {
                    continue;
                }

                $namaKaryawan = str($report->user->nama_karyawan ?? 'unknown')
                    ->slug('_')
                    ->toString();

                $namaFile = $namaKaryawan . '_' . $report->daily_report_id . '.pdf';

                $zip->addFile($filePath, $namaFile);
                $totalRows++;
            }

            $zip->close();
        }

        ExportDailyReport::create([
            'filename'   => $filename,
            'path'       => $storagePath,
            'url'        => asset('storage/' . $storagePath),
            'total_rows' => $totalRows,
        ]);

        cache()->put('export_done', true, now()->addMinutes(1));
    }
}
