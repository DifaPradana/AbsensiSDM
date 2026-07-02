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

class ExportDailyReportMonthlyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $awalBulan  = now()->startOfMonth();
        $akhirBulan = now()->endOfMonth();

        $filename    = 'daily-report-' . now()->translatedFormat('F-Y') . '.zip';
        $storagePath = 'exports/' . $filename;
        $fullPath    = storage_path('app/public/' . $storagePath);

        if (! file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $reports = DailyReport::with('user')
            ->whereHas('user')
            ->whereBetween('created_at', [$awalBulan, $akhirBulan])
            ->latest()
            ->get();

        $zip       = new ZipArchive();
        $totalRows = 0;

        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $usedNames = [];

            foreach ($reports as $report) {
                $filePath = storage_path('app/public/' . $report->path_dokumen);

                if (! file_exists($filePath)) {
                    continue;
                }

                $namaKaryawan = str_replace(
                    ' ',
                    '_',
                    ucwords($report->user->nama_karyawan ?? 'unknown')
                );

                $tanggal  = $report->created_at->format('d-m-Y');
                $namaFile = $namaKaryawan . '_' . $tanggal . '.pdf';

                // Hindari nama file bentrok di dalam folder karyawan yang sama
                // (misal ada 2 laporan di tanggal yang sama)
                $key = $namaKaryawan . '/' . $namaFile;

                if (isset($usedNames[$key])) {
                    $usedNames[$key]++;
                    $namaFile = $namaKaryawan . '_' . $tanggal . '_' . $usedNames[$key] . '.pdf';
                } else {
                    $usedNames[$key] = 1;
                }

                // Path di dalam zip: NamaKaryawan/NamaKaryawan_tanggal.pdf
                $pathDalamZip = $namaKaryawan . '/' . $namaFile;

                $zip->addFile($filePath, $pathDalamZip);
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

        cache()->put('export_monthly_done', true, now()->addMinutes(1));
    }
}
