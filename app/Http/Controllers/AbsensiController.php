<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Lokasi_absensi;
use App\Models\LokasiAbsensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    public function getAllAbsen(Request $request)
    {
        $query = Absensi::query();

        // optional sorting
        if ($request->has('sort')) {

            if ($request->sort == 'oldest') {
                $query->orderBy('created_at', 'asc');
            }

            if ($request->sort == 'newest') {
                $query->orderBy('created_at', 'desc');
            }
        }

        $absensi = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $absensi
        ]);
    }


    public function getAllMyAbsen()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Karyawan tidak ditemukan'
            ], 404);
        }

        $kehadiran = $user->absensis()
            ->paginate(20);

        $kehadiran->getCollection()->transform(function ($item) {
            $item->photo_masuk = $item->photo_masuk
                ? asset('storage/' . $item->photo_masuk)
                : null;

            $item->photo_pulang = $item->photo_pulang
                ? asset('storage/' . $item->photo_pulang)
                : null;

            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $kehadiran
        ]);
    }

    public function absen(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user->role;

        $absensiAktif = Absensi::where('user_id', $user->user_id)
            ->whereNull('waktu_absen_pulang')
            ->latest('waktu_absen_masuk')
            ->first();

        $isAbsenMasuk = !$absensiAktif;

        if ($isAbsenMasuk) {

            $sudahSelesai = Absensi::where('user_id', $user->user_id)
                ->whereDate('waktu_absen_masuk', today())
                ->whereNotNull('waktu_absen_pulang')
                ->exists();

            if ($sudahSelesai) {

                return response()->json([
                    'status' => 'error',
                    'message' => 'Kamu sudah melakukan absensi hari ini'
                ], 400);
            }
        }

        $messages = [
            'latitude_masuk.required' => 'Latitude harus diisi',
            'longitude_masuk.required' => 'Longitude harus diisi',
            'latitude_masuk.max' => 'Latitude terlalu panjang',
            'longitude_masuk.max' => 'Longitude terlalu panjang',
            'latitude_pulang.required' => 'Latitude harus diisi',
            'longitude_pulang.required' => 'Longitude harus diisi',
            'latitude_pulang.max' => 'Latitude terlalu panjang',
            'longitude_pulang.max' => 'Longitude terlalu panjang',

            'photo_masuk.image' => 'File harus berupa gambar',
            'photo_masuk.mimes' => 'Photo harus jpg, jpeg, atau png',
            'photo_masuk.max' => 'Ukuran photo maksimal 10 MB',
            'photo_pulang.image' => 'File harus berupa gambar',
            'photo_pulang.mimes' => 'Photo harus jpg, jpeg, atau png',
            'photo_pulang.max' => 'Ukuran photo maksimal 10 MB',
            'note.max' => 'Note maksimal 100 karakter'
        ];

        $rules = [
            'latitude_masuk' => 'required|string|max:20',
            'longitude_masuk' => 'required|string|max:20',
            'latitude_pulang' => 'required|string|max:20',
            'longitude_pulang' => 'required|string|max:20',
            'photo_masuk' => 'nullable|file|image|mimes:jpg,jpeg,png|max:10000',
            'photo_pulang' => 'nullable|file|image|mimes:jpg,jpeg,png|max:10000',
            'lokasi' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:100',
        ];

        if ($isAbsenMasuk) {
            $rules = [
                'latitude_masuk' => 'required|string|max:20',
                'longitude_masuk' => 'required|string|max:20',
                'photo_masuk' => 'required|file|image|mimes:jpg,jpeg,png|max:10000',
                'note_masuk' => 'nullable|string|max:100',
            ];
        } else {
            $rules = [
                'latitude_pulang' => 'required|string|max:20',
                'longitude_pulang' => 'required|string|max:20',
                'photo_pulang' => 'required|file|image|mimes:jpg,jpeg,png|max:10000',
                'lokasi_pulang' => 'nullable|string|max:100',
            ];
        }

        $validate = Validator::make(
            $request->all(),
            $rules,
            $messages
        );

        if ($validate->fails()) {

            return response()->json([
                'status' => 'error',
                'message' => $validate->errors()
            ], 400);
        }

        $latitude = $isAbsenMasuk
            ? $request->latitude_masuk
            : $request->latitude_pulang;

        $longitude = $isAbsenMasuk
            ? $request->longitude_masuk
            : $request->longitude_pulang;

        $namaLokasi = 'Unknown';

        foreach (LokasiAbsensi::all() as $lokasi) {

            $jarak = $this->hitungJarakMeter(
                (float) $latitude,
                (float) $longitude,
                (float) $lokasi->latitude_lokasi,
                (float) $lokasi->longitude_lokasi
            );

            if ($jarak <= $lokasi->radius_meter) {
                $namaLokasi = $lokasi->nama_lokasi;
                break;
            }
        }


        $photoField = $isAbsenMasuk ? 'photo_masuk' : 'photo_pulang';
        $uploadedFile = $request->file($photoField);

        $filename = Str::uuid() . '.' . $uploadedFile->extension();
        $relativePath = 'absensi/' . $filename;
        $fullPath = storage_path('app/public/' . $relativePath);

        $directory = dirname($fullPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodeSplFileInfo($uploadedFile);

        $image->scale(width: 1200);

        $image->save($fullPath, quality: 70);

        $photoPath = $relativePath;


        $jadwalMasukRole = today()->setTimeFromTimeString($role->jadwal_masuk);
        $waktuAbsen = Carbon::parse($request->waktu_absen_masuk);

        $toleransi = $role->toleransi_keterlambatan;

        $statusAbsensiMasuk = 'On Time';


        $selisihMenit = $jadwalMasukRole->diffInMinutes($waktuAbsen);

        $statusAbsensiMasuk = match (true) {
            $selisihMenit <= $toleransi => 'On Time',
            $selisihMenit > 60 => 'Terlambat lebih dari 1 Jam',
            $selisihMenit > 20 => 'Terlambat lebih dari 20 Menit',
            $selisihMenit > 10 => 'Terlambat lebih dari 10 Menit',
            default => 'On Time',
        };

        // dd([
        //     'now' => now()->toDateTimeString(),
        //     'timezone' => now()->timezoneName,
        // ]);

        if ($isAbsenMasuk) {


            Absensi::create([
                'user_id' => $user->user_id,

                'waktu_absen_masuk' => now(),
                'lokasi_masuk' => $namaLokasi,
                'latitude_masuk' => $request->latitude_masuk,
                'longitude_masuk' => $request->longitude_masuk,
                'status_absensi_masuk' => $statusAbsensiMasuk,
                'photo_masuk' => $photoPath,
                'note_masuk' => $request->note_masuk
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil melakukan absensi masuk'
            ], 201);
        }

        $jadwalPulangRole = today()->setTimeFromTimeString($role->jadwal_pulang);
        $waktuAbsen = Carbon::parse($request->waktu_absen_pulang);

        $toleransi = $role->toleransi_keterlambatan;

        $statusAbsensiPulang = 'On Time';

        // Lembur hanya jika > 60 menit setelah jadwal pulang
        if ($waktuAbsen->greaterThan($jadwalPulangRole->copy()->addMinutes(60))) {
            $selisihMenit = $jadwalPulangRole->diffInMinutes($waktuAbsen);

            $selisihMenit = (int) round($selisihMenit);
            if ($selisihMenit >= 60) {
                $jam = floor($selisihMenit / 60);
                $menit = $selisihMenit % 60;

                $statusAbsensiPulang = $menit > 0
                    ? "Lembur {$jam} Jam {$menit} Menit"
                    : "Lembur {$jam} Jam";
            }
        } elseif ($waktuAbsen->lessThan($jadwalPulangRole->copy()->subMinutes($toleransi))) {

            // Pulang lebih cepat (pakai toleransi)
            $selisihMenit = $waktuAbsen->diffInMinutes($jadwalPulangRole);
            $selisihMenit = (int) round($selisihMenit);
            if ($selisihMenit >= 60) {
                $jam = floor($selisihMenit / 60);
                $menit = $selisihMenit % 60;

                $statusAbsensiPulang = $menit > 0
                    ? "Pulang Lebih Cepat {$jam} Jam {$menit} Menit"
                    : "Pulang Lebih Cepat {$jam} Jam";
            } else {
                $statusAbsensiPulang = "Pulang Lebih Cepat {$selisihMenit} Menit";
            }
        }



        // dd($statusAbsensiPulang);

        $absensiAktif->update([
            'waktu_absen_pulang' => now(),
            'latitude_pulang' => $request->latitude_pulang,
            'longitude_pulang' => $request->longitude_pulang,
            'photo_pulang' => $photoPath,
            'status_absensi_pulang' => $statusAbsensiPulang,
            'lokasi_pulang' => $namaLokasi,
            'note_pulang' => $request->note_pulang
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil melakukan absensi pulang'
        ], 200);
    }


    // public function getTodayAbsen(Request $request)
    // {
    //     /** @var \App\Models\User $user */
    //     $user = Auth::user();

    //     $absen_datang = Absensi::where('user_id', $user->user_id)
    //         ->whereDate('waktu_absen_masuk', today())
    //         ->first();

    //     $absen_pulang = Absensi::where('user_id', $user->user_id)
    //         ->whereDate('waktu_absen_pulang', today())
    //         ->first();


    //     if (!$absen_datang) {
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Belum absen pagi'
    //         ], 200);
    //     }

    //     if (!$absen_pulang) {
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'Belum absen pulang'
    //         ], 200);
    //     }

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Absensi hari ini sudah lengkap'
    //     ]);
    // }

    private function hitungJarakMeter(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) *
            sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }


    public function getTodayAbsen(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $kehadiran = $user->absensis()
            ->whereDate('waktu_absen_masuk', today())
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $kehadiran
        ], 200);
    }
}
