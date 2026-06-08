<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\LokasiAbsensi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::create([
            'nama_role' => 'Admin',
            'jadwal_masuk' => '12:00:00',
            'jadwal_pulang' => '12:00:00',
        ]);

        Role::create([
            'nama_role' => 'Pegawai Kantor',
            'jadwal_masuk' => '08:00:00',
            'jadwal_pulang' => '17:00:00',
        ]);

        Role::create([
            'nama_role' => 'Pegawai Lapangan',
            'jadwal_masuk' => '07:00:00',
            'jadwal_pulang' => '16:00:00',
        ]);

        Role::create([
            'nama_role' => 'Test Role',
            'jadwal_masuk' => '08:30:00',
            'jadwal_pulang' => '10:00:00',
        ]);

        User::create([
            'nama_karyawan' => 'admin',
            'is_active' => true,
            'is_password_default' => false,
            'password' => '61b838edadbae4df9b75',
            'role_id' => 1,
        ]);

        User::create([
            'nama_karyawan' => 'difa pradana',
            'is_active' => true,
            'is_password_default' => true,
            'password' => 'dips',
            'role_id' => 2,
        ]);

        User::create([
            'nama_karyawan' => 'testapi',
            'is_active' => true,
            'is_password_default' => false,
            'password' => 'loremipsum',
            'role_id' => 2,
        ]);

        LokasiAbsensi::create([
            'nama_lokasi' => 'Kantor SDM',
            'latitude_lokasi' => '-7.6741565',
            'longitude_lokasi' => '109.030041',
            'radius_meter' => 50
        ]);

        // Absensi::create([
        //     'user_id' => 3,
        //     'tipe_absensi' => 'masuk',
        //     'photo_masuk' => 'absensi/9746821a-1dc0-4ee9-8a36-8249933a1d27.jpg',
        //     'photo_pulang' => null,
        //     'waktu_absen_masuk' => '2026-06-08 16:25:33',
        //     'waktu_absen_pulang' => '2026-06-08 17:00:45',
        //     'latitude_masuk' => -7.685434,
        //     'longitude_masuk' => 109.042733,
        //     'latitude_pulang' => -7.685434,
        //     'longitude_pulang' => 109.042733,
        //     'status_absensi_masuk' => 'Terlambat lebih dari 1 Jam',
        //     'status_absensi_pulang' => 'On Time',
        //     'lokasi_masuk' => 'Unknown',
        //     'lokasi_pulang' => 'Unknown',
        // ]);
        // Absensi::create([
        //     'user_id' => 3,
        //     'tipe_absensi' => 'masuk',
        //     'photo_masuk' => 'absensi/9746821a-1dc0-4ee9-8a36-8249933a1d27.jpg',
        //     'photo_pulang' => null,
        //     'waktu_absen_masuk' => '2026-06-03 09:57:45',
        //     'waktu_absen_pulang' => '2026-06-03 17:00:45',
        //     'latitude_masuk' => -7.674183,
        //     'longitude_masuk' => 109.030026,
        //     'latitude_pulang' => -7.674183,
        //     'longitude_pulang' => 109.030026,
        //     'status_absensi_masuk' => 'Terlambat lebih dari 1 Jam',
        //     'status_absensi_pulang' => 'On Time',
        //     'lokasi_masuk' => 'Kantor SDM',
        //     'lokasi_pulang' => 'Kantor SDM',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);
        // Absensi::create([
        //     'user_id' => 3,
        //     'tipe_absensi' => 'masuk',
        //     'photo_masuk' => 'absensi/9746821a-1dc0-4ee9-8a36-8249933a1d27.jpg',
        //     'photo_pulang' => null,
        //     'waktu_absen_masuk' => '2026-06-02 09:57:45',
        //     'waktu_absen_pulang' => '2026-06-02 17:00:45',
        //     'latitude_masuk' => -7.674183,
        //     'longitude_masuk' => 109.030026,
        //     'latitude_pulang' => -7.674183,
        //     'longitude_pulang' => 109.030026,
        //     'status_absensi_masuk' => 'Terlambat lebih dari 1 Jam',
        //     'status_absensi_pulang' => 'On Time',
        //     'lokasi_masuk' => 'Kantor SDM',
        //     'lokasi_pulang' => 'Kantor SDM',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);

        // Absensi::create([
        //     'user_id' => 3,
        //     'tipe_absensi' => 'masuk',
        //     'photo_masuk' => 'absensi/9746821a-1dc0-4ee9-8a36-8249933a1d27.jpg',
        //     'photo_pulang' => null,
        //     'waktu_absen_masuk' => '2026-06-01 09:57:45',
        //     'waktu_absen_pulang' => '2026-06-01 17:00:45',
        //     'latitude_masuk' => -7.674183,
        //     'longitude_masuk' => 109.030026,
        //     'latitude_pulang' => -7.674183,
        //     'longitude_pulang' => 109.030026,
        //     'status_absensi_masuk' => 'Terlambat lebih dari 1 Jam',
        //     'status_absensi_pulang' => 'On Time',
        //     'lokasi_masuk' => 'Kantor SDM',
        //     'lokasi_pulang' => 'Kantor SDM',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);

        // Absensi::create([
        //     'user_id' => 3,
        //     'tipe_absensi' => 'masuk',
        //     'photo_masuk' => 'absensi/9746821a-1dc0-4ee9-8a36-8249933a1d27.jpg',
        //     'photo_pulang' => null,
        //     'waktu_absen_masuk' => '2026-05-31 09:57:45',
        //     'waktu_absen_pulang' => '2026-05-31 17:00:45',
        //     'latitude_masuk' => -7.674183,
        //     'longitude_masuk' => 109.030026,
        //     'latitude_pulang' => -7.674183,
        //     'longitude_pulang' => 109.030026,
        //     'status_absensi_masuk' => 'Terlambat lebih dari 1 Jam',
        //     'status_absensi_pulang' => 'On Time',
        //     'lokasi_masuk' => 'Kantor SDM',
        //     'lokasi_pulang' => 'Kantor SDM',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);

        // Absensi::create([
        //     'user_id' => 3,
        //     'tipe_absensi' => 'masuk',
        //     'photo_masuk' => 'absensi/9746821a-1dc0-4ee9-8a36-8249933a1d27.jpg',
        //     'photo_pulang' => null,
        //     'waktu_absen_masuk' => '2026-05-30 09:57:45',
        //     'waktu_absen_pulang' => '2026-05-30 17:00:45',
        //     'latitude_masuk' => -7.674183,
        //     'longitude_masuk' => 109.030026,
        //     'latitude_pulang' => -7.674183,
        //     'longitude_pulang' => 109.030026,
        //     'status_absensi_masuk' => 'Terlambat lebih dari 1 Jam',
        //     'status_absensi_pulang' => 'On Time',
        //     'lokasi_masuk' => 'Kantor SDM',
        //     'lokasi_pulang' => 'Kantor SDM',
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);
    }
}
