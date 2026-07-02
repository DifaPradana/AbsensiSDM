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
        protected ?string $search = null,
    ) {}

    public function handle(): void
    {
        $filename    = 'daily-report-' . now()->translatedFormat('F-Y') . '.zip';
        $storagePath = 'exports/' . $filename;
        $fullPath    = storage_path('app/public/' . $storagePath);

        if (! file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $reports = DailyReport::with('user')
            ->whereHas('user')
            ->when($this->search, fn($q) => $q->whereHas(
                'user',
                fn($u) =>
                $u->whereRaw('LOWER(nama_karyawan) LIKE ?', ['%' . strtolower($this->search) . '%'])
            ))
            ->when($this->tanggalAwal,  fn($q) => $q->whereDate('created_at', '>=', $this->tanggalAwal))
            ->when($this->tanggalAkhir, fn($q) => $q->whereDate('created_at', '<=', $this->tanggalAkhir))
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

                if (isset($usedNames[$namaFile])) {
                    $usedNames[$namaFile]++;
                    $namaFile = $namaKaryawan . '_' . $tanggal . '_' . $usedNames[$namaFile] . '.pdf';
                } else {
                    $usedNames[$namaFile] = 1;
                }

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
