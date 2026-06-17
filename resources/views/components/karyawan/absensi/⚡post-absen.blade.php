<?php

use App\Models\Absensi;
use App\Models\LokasiAbsensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $latitude;
    public $longitude;
    public $photo;
    public $note = '';
    public bool $gpsReady = false;

    public function absen()
    {
        $user = Auth::user();
        $role = $user->role;

        // dd($this->all());

        $absensiAktif = Absensi::where('user_id', $user->user_id)
            ->whereDate('waktu_absen_masuk', today())
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
                $this->dispatch('absen-error', message: 'Kamu sudah melakukan absensi hari ini');
                return;
            }
        }

        $validate = Validator::make([
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'photo'     => $this->photo,
            'note'      => $this->note,
        ], [
            'latitude'  => 'required|string|max:20',
            'longitude' => 'required|string|max:20',
            'photo'     => 'required|file|image|mimes:jpg,jpeg,png|max:10000',
            'note'      => 'nullable|string|max:100',
        ], [
            'latitude.required'  => 'Latitude harus diisi',
            'longitude.required' => 'Longitude harus diisi',
            'photo.required'     => 'Foto harus diambil',
            'photo.image'        => 'File harus berupa gambar',
            'photo.mimes'        => 'Photo harus jpg, jpeg, atau png',
            'photo.max'          => 'Ukuran photo maksimal 10 MB',
            'note.max'           => 'Catatan maksimal 100 karakter',
        ]);

        if ($validate->fails()) {
            $this->dispatch('absen-error', message: $validate->errors()->first());
            LivewireAlert::title($validate->errors()->first())
                ->error()
                ->timer(null)
                ->toast()
                ->withConfirmButton('Ok')
                ->withOptions(['allowOutsideClick' => false])
                ->show();
            return;
        }

        $namaLokasi = 'Unknown';
        foreach (LokasiAbsensi::all() as $lokasi) {
            $jarak = $this->hitungJarakMeter(
                (float) $this->latitude,
                (float) $this->longitude,
                (float) $lokasi->latitude_lokasi,
                (float) $lokasi->longitude_lokasi
            );
            if ($jarak <= $lokasi->radius_meter) {
                $namaLokasi = $lokasi->nama_lokasi;
                break;
            }
        }

        $photoField = $isAbsenMasuk ? 'photo_masuk' : 'photo_pulang';
        $uploadedFile = $this->photo;

        $filename = Str::uuid() . '.' . $uploadedFile->extension();
        $relativePath = 'absensi/' . $filename;
        $fullPath = storage_path('app/public/' . $relativePath);

        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $manager = ImageManager::usingDriver(Driver::class);

        $image = $manager->decodeSplFileInfo($uploadedFile);

        $image->scale(width: 1200);

        $image->save($fullPath, quality: 70);

        $photoPath = $relativePath;

        if ($isAbsenMasuk) {
            $jadwalMasukRole = today()->setTimeFromTimeString($role->jadwal_masuk);
            $waktuAbsen      = Carbon::now();
            $selisihMenit    = $jadwalMasukRole->diffInMinutes($waktuAbsen, false);
            $toleransi       = $role->toleransi_keterlambatan;

            $statusAbsensiMasuk = match (true) {
                $selisihMenit <= $toleransi => 'On Time',
                $selisihMenit > 60          => 'Terlambat lebih dari 1 Jam',
                $selisihMenit > 20          => 'Terlambat lebih dari 20 Menit',
                $selisihMenit > 10          => 'Terlambat lebih dari 10 Menit',
                default                     => 'On Time',
            };

            Absensi::create([
                'user_id'              => $user->user_id,
                'waktu_absen_masuk'    => now(),
                'lokasi_masuk'         => $namaLokasi,
                'latitude_masuk'       => $this->latitude,
                'longitude_masuk'      => $this->longitude,
                'status_absensi_masuk' => $statusAbsensiMasuk,
                'photo_masuk'          => $photoPath,
                'note_masuk'           => $this->note,
            ]);


            LivewireAlert::title('Absensi Berhasil')
                ->success()
                ->withOptions(['allowOutsideClick' => false])
                ->withConfirmButton('OK')
                ->onConfirm('reloadPage')
                ->show();

            $this->dispatch('absen-success');
            return;
        }

        $jadwalPulangRole    = today()->setTimeFromTimeString($role->jadwal_pulang);
        $waktuAbsen          = Carbon::now();
        $toleransi           = $role->toleransi_keterlambatan;
        $statusAbsensiPulang = 'On Time';

        if ($waktuAbsen->greaterThan($jadwalPulangRole->copy()->addMinutes(60))) {
            $selisihMenit = (int) round($jadwalPulangRole->diffInMinutes($waktuAbsen));
            if ($selisihMenit >= 60) {
                $jam   = floor($selisihMenit / 60);
                $menit = $selisihMenit % 60;
                $statusAbsensiPulang = $menit > 0 ? "Lembur {$jam} Jam {$menit} Menit" : "Lembur {$jam} Jam";
            }
        } elseif ($waktuAbsen->lessThan($jadwalPulangRole->copy()->subMinutes($toleransi))) {
            $selisihMenit = (int) round($waktuAbsen->diffInMinutes($jadwalPulangRole));
            if ($selisihMenit >= 60) {
                $jam   = floor($selisihMenit / 60);
                $menit = $selisihMenit % 60;
                $statusAbsensiPulang = $menit > 0 ? "Pulang Lebih Cepat {$jam} Jam {$menit} Menit" : "Pulang Lebih Cepat {$jam} Jam";
            } else {
                $statusAbsensiPulang = "Pulang Lebih Cepat {$selisihMenit} Menit";
            }
        }

        $absensiAktif->update([
            'waktu_absen_pulang'    => now(),
            'latitude_pulang'       => $this->latitude,
            'longitude_pulang'      => $this->longitude,
            'photo_pulang'          => $photoPath,
            'status_absensi_pulang' => $statusAbsensiPulang,
            'lokasi_pulang'         => $namaLokasi,
            'note_pulang'           => $this->note,
        ]);


        LivewireAlert::title('Absensi Berhasil')
            ->success()
            ->withOptions(['allowOutsideClick' => false])
            ->withConfirmButton('OK')
            ->onConfirm('reloadPage')
            ->show();

        $this->dispatch('absen-success');
        return;
    }

    private function hitungJarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
    #[On('absen-success')]
    public function reloadPage()
    {
        $this->redirectRoute('karyawan.kehadiran.page');
    }
};
?>


<div>
    <form wire:submit.prevent="absen">
        <div class="card">
            <div class="card-body">
                <input type="hidden" wire:model.live="latitude" id="latitude">
                <input type="hidden" wire:model.live="longitude" id="longitude">
                <p class="section-label">Foto Selfie</p>
                <input type="file" wire:model="photo" accept="image/*" capture="user" class="form-control" id="photo">
                <div wire:loading wire:target="photo" class="text-primary mt-2">
                    <span class="spinner-border spinner-border-sm"></span>
                    Uploading...
                </div>

                <div class="divider"></div>

                <p class="section-label">Lokasi GPS</p>
                <div wire:ignore class="gps-row">
                    <i class="ti ti-map-pin" style="font-size:20px;color:var(--color-text-secondary);"></i>
                    <div class="gps-text">
                        <p id="gps-status-text">Mengambil lokasi...</p>
                        <span id="gps-coords">Mohon izinkan akses lokasi</span>
                    </div>
                    <span class="badge badge-warning" id="gps-badge"><span class="dot"></span> Menunggu</span>
                </div>

                <div class="divider"></div>

                <p class="section-label">Catatan</p>
                <textarea wire:model="note" placeholder="Tambahkan catatan (opsional)..." class="form-control"></textarea>

                <div class="divider"></div>

                <button
                    type="submit"
                    class="btn btn-primary"
                    style="width:100%;justify-content:center;"
                    wire:loading.attr="disabled"
                    wire:target="photo, absen"
                    @disabled(!$gpsReady)>
                    <i class="ti ti-send" style="font-size:15px;"></i>
                    <span>Kirim Presensi</span>
                </button>
            </div>
        </div>
    </form>


    <script>
        document.querySelector('form').addEventListener('submit', function() {
            $wire.set('latitude', document.getElementById('latitude').value);
            $wire.set('longitude', document.getElementById('longitude').value);
            component.$wire.set('gpsReady', true);
        });

        document.addEventListener('livewire:init', () => {
            Livewire.on('reload-page', () => {
                alert('reload-page diterima');
                window.location.reload();
            });
        });



        (function() {
            const alertBox = document.getElementById('alert-box');
            const gpsStatusText = document.getElementById('gps-status-text');
            const gpsCoordsEl = document.getElementById('gps-coords');
            const gpsBadge = document.getElementById('gps-badge');
            const openGpsBtn = document.getElementById('open-gps-btn');


            function getLocation() {

                if (!navigator.geolocation) {
                    gpsStatusText.textContent = 'Browser tidak mendukung GPS';
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    pos => {
                        // alert('Masuk callback GPS');

                        // Livewire.all().forEach(c => {
                        // alert(c.name);
                        // });

                        const lat = pos.coords.latitude.toFixed(7);
                        const lng = pos.coords.longitude.toFixed(7);
                        const acc = Math.round(pos.coords.accuracy);

                        // alert(lat);

                        gpsStatusText.textContent = 'Lokasi ditemukan';
                        gpsCoordsEl.textContent = `${lat}, ${lng} (±${acc} m)`;

                        gpsBadge.innerHTML =
                            '<i class="ti ti-check" style="font-size:12px;"></i> Terkunci';

                        gpsBadge.className = 'badge badge-success';

                        // alert('1');

                        try {
                            let component = Livewire.all()[1];

                            component.$wire.set('latitude', lat);
                            component.$wire.set('longitude', lng);

                            // alert('berhasil');
                        } catch (e) {
                            window.location.reload();
                        }

                    },
                    err => {
                        gpsStatusText.textContent = 'Akses lokasi gagal';

                        gpsBadge.innerHTML =
                            '<i class="ti ti-x" style="font-size:12px;"></i> GPS Ditolak';

                        gpsBadge.className = 'badge badge-danger';

                        gpsCoordsEl.textContent = err.message;
                        $wire.set('gpsReady', false);
                    }
                );
            }

            getLocation();

            openGpsBtn.addEventListener('click', getLocation);
            // ─── Livewire events ──────────────────────────────────────────
            window.addEventListener('absen-success', e => {
                const {
                    message
                } = e.detail[0] ?? e.detail;
                alertBox.className = 'alert alert-success';
                alertBox.textContent = message;
                alertBox.style.display = 'block';
            });

            window.addEventListener('absen-error', e => {
                const {
                    message
                } = e.detail[0] ?? e.detail;
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = message;
                alertBox.style.display = 'block';
                setTimeout(() => alertBox.style.display = 'none', 5000);
            });
        })();
    </script>
</div>