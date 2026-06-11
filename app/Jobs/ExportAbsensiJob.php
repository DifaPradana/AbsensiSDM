<?php

namespace App\Jobs;

use App\Models\Absensi;
use App\Models\ExportAbsen;
use App\Models\ExportFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportAbsensiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected ?string $search,
        protected ?string $tanggalAwal,
        protected ?string $tanggalAkhir,
    ) {}

    public function handle(): void
    {
        $filename = 'absensi_' . now()->format('Ymd_His') . '.csv';
        $storagePath = 'exports/' . $filename;
        $fullPath = storage_path('app/public/' . $storagePath);

        if (! file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $query = Absensi::with('user')
            ->whereHas('user')
            ->search($this->search)
            ->filterTanggal($this->tanggalAwal, $this->tanggalAkhir)
            ->latest();

        $file = fopen($fullPath, 'w');

        fputcsv($file, [
            'Tanggal',
            'Nama',
            'Role',
            'Status',
            'Waktu Masuk',
            'Status Masuk',
            'Lokasi Masuk',
            'Note Masuk',
            'Waktu Pulang',
            'Status Pulang',
            'Lokasi Pulang',
            'Note Pulang',
        ]);

        $totalRows = 0;

        $query->chunk(200, function ($rows) use ($file, &$totalRows) {
            foreach ($rows as $absensi) {
                fputcsv($file, [
                    $absensi->waktu_absen_masuk?->translatedFormat('l, d F') ?? '-',
                    $absensi->user->nama_karyawan ?? '-',
                    $absensi->user->role->nama_role ?? '-',
                    ucfirst($absensi->tipe_absensi ?? '-'),
                    $absensi->waktu_absen_masuk?->format('H:i') ?? '-',
                    $absensi->status_absensi_masuk ?? '-',
                    $absensi->lokasi_masuk ?? '-',
                    // $absensi->note_masuk ?? '-',
                    $absensi->waktu_absen_pulang?->format('H:i') ?? '-',
                    $absensi->status_absensi_pulang ?? '-',
                    $absensi->lokasi_pulang ?? '-',
                    // $absensi->note_pulang ?? '-',
                ]);
                $totalRows++;
            }
        });

        fclose($file);

        ExportAbsen::create([
            'filename'   => $filename,
            'path'       => $storagePath,
            'url'        => asset('storage/' . $storagePath),
            'total_rows' => $totalRows,
        ]);

        cache()->put('export_done', true, now()->addMinutes(1)); // ✅
    }
}
