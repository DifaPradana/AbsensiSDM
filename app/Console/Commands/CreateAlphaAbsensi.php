<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
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

        // Ambil semua user aktif kecuali admin & direktur
        $users = User::with('role')
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->whereHas(
                'role',
                fn($q) => $q
                    ->whereNotIn('nama_role', ['admin', 'direktur'])
            )
            ->get();

        // Filter user yang belum punya absensi hari ini
        $userTidakAbsen = $users->filter(
            fn($user) =>
            !Absensi::where('user_id', $user->user_id)
                ->whereDate('waktu_absen_masuk', today())
                ->exists()
        );

        if ($userTidakAbsen->isEmpty()) {
            $this->info('Semua user sudah absen hari ini.');
            return self::SUCCESS;
        }

        $this->table(
            ['User ID', 'Nama', 'Role', 'Aksi'],
            $userTidakAbsen->map(fn($user) => [
                $user->user_id,
                $user->nama_karyawan,
                $user->role->nama_role,
                $dryRun ? '⏭ Skip (dry-run)' : '✅ Dibuat alpha',
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn("Dry-run aktif. {$userTidakAbsen->count()} record TIDAK dibuat.");
            return self::SUCCESS;
        }

        foreach ($userTidakAbsen as $user) {
            Absensi::create([
                'user_id'              => $user->user_id,
                'tipe_absensi'         => 'alpha',
                'waktu_absen_masuk'    => null,
                'waktu_absen_pulang'   => null,
                'latitude_masuk'       => 0,
                'longitude_masuk'      => 0,
                'latitude_pulang'       => 0,
                'longitude_pulang'      => 0,
                'status_absensi_masuk' => '-',
                'status_absensi_pulang' => '-',
                'lokasi_masuk'         => '-',
                'lokasi_pulang'         => '-',
            ]);
        }

        $this->info("✅ {$userTidakAbsen->count()} record alpha berhasil dibuat.");
        return self::SUCCESS;
    }
}
