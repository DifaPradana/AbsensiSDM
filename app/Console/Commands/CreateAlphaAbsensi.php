<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\IzinAbsen;
use App\Models\User;
use Illuminate\Console\Command;

class CreateAlphaAbsensi extends Command
{
    protected $signature = 'absensi:create-alpha {--dry-run}';
    protected $description = 'Buat record alpha untuk user yang tidak absen hari ini';

    public function handle(): int
    {
        if (now()->isWeekend()) {
            $this->info('Hari libur, command tidak dijalankan.');
            return self::SUCCESS;
        }

        $dryRun = $this->option('dry-run');

        // Ambil semua user aktif kecuali admin, direktur, hrd
        $users = User::with('role')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->whereHas(
                'role',
                fn($q) => $q->whereNotIn('nama_role', ['admin', 'direktur', 'hrd'])
            )
            ->get();

        // Filter user yang perlu dibuat alpha
        $userTidakAbsen = $users->filter(function ($user) {
            // Skip jika sudah ada absensi nyata (bukan alpha) hari ini
            $sudahAbsenNyata = Absensi::where('user_id', $user->user_id)
                ->whereDate('waktu_absen_masuk', today())
                ->where('tipe_absensi', '!=', 'alpha')
                ->exists();

            if ($sudahAbsenNyata) return false;

            // Skip jika ada izin yang sudah disetujui mencakup hari ini
            $adaIzinDisetujui = IzinAbsen::where('user_id', $user->user_id)
                ->where('status', 'disetujui')
                ->whereDate('mulai_izin', '<=', today())
                ->where(function ($q) {
                    $q->whereDate('akhir_izin', '>=', today())
                        ->orWhereNull('akhir_izin');
                })
                ->exists();

            if ($adaIzinDisetujui) return false;

            return true;
        });

        if ($userTidakAbsen->isEmpty()) {
            $this->info('Semua user sudah absen atau memiliki izin hari ini.');
            return self::SUCCESS;
        }

        $this->table(
            ['User ID', 'Nama', 'Role', 'Aksi'],
            $userTidakAbsen->map(fn($user) => [
                $user->user_id,
                $user->nama_karyawan,
                $user->role->nama_role,
                $dryRun ? '⏭ Skip (dry-run)' : '✅ Akan dibuat alpha',
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn("Dry-run aktif. {$userTidakAbsen->count()} record TIDAK dibuat.");
            return self::SUCCESS;
        }

        $berhasil = 0;
        $dilewati = 0;
        $gagal    = 0;

        foreach ($userTidakAbsen as $user) {
            try {
                // Cek lagi saat loop — jaga-jaga alpha sudah dibuat sebelumnya
                // (misal command dijalankan 2x sehari)
                $sudahAdaAlpha = Absensi::where('user_id', $user->user_id)
                    ->whereDate('waktu_absen_masuk', today())
                    ->where('tipe_absensi', 'alpha')
                    ->exists();

                if ($sudahAdaAlpha) {
                    $this->line("⏭  Skip {$user->nama_karyawan} — alpha sudah ada.");
                    $dilewati++;
                    continue;
                }

                Absensi::create([
                    'user_id'               => $user->user_id,
                    'tipe_absensi'          => 'alpha',
                    'waktu_absen_masuk'     => today()->setTimeFromTimeString($user->role->jadwal_masuk),
                    'waktu_absen_pulang'    => today()->setTimeFromTimeString($user->role->jadwal_pulang),
                    'latitude_masuk'        => 0,
                    'longitude_masuk'       => 0,
                    'latitude_pulang'       => 0,
                    'longitude_pulang'      => 0,
                    'status_absensi_masuk'  => '-',
                    'status_absensi_pulang' => '-',
                    'lokasi_masuk'          => '-',
                    'lokasi_pulang'         => '-',
                ]);

                $berhasil++;
            } catch (\Throwable $e) {
                $this->error("Gagal: {$user->nama_karyawan} — {$e->getMessage()}");
                $gagal++;
            }
        }

        $this->newLine();
        $this->info("✅ {$berhasil} record alpha berhasil dibuat.");

        if ($dilewati > 0) {
            $this->line("⏭  {$dilewati} dilewati (alpha sudah ada).");
        }

        if ($gagal > 0) {
            $this->warn("⚠️  {$gagal} gagal dibuat.");
        }

        return $gagal > 0 ? self::FAILURE : self::SUCCESS;
    }
}
