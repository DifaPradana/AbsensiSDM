    <?php

    use App\Models\LokasiAbsensi;
    use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
    use Livewire\Component;

    new class extends Component
    {
        public $nama_lokasi;
        public $latitude_lokasi;
        public $longitude_lokasi;
        public $radius_meter;


        public function daftarin()
        {
            $message = [
                'nama_lokasi.string' => 'Nama lokasi harus berupa teks',
                'nama_lokasi.required' => 'Nama lokasi wajib diisi',
                'nama_lokasi.min' => 'Nama lokasi minimal 3 karakter',
                'nama_lokasi.max' => 'Nama lokasi maksimal 100 karakter',
                'latitude_lokasi.required' => 'Latitude wajib diisi',
                'longitude_lokasi.required' => 'Longitude wajib diisi',
                'longitude_lokasi.between' => 'Longitude harus berada antara -180 sampai 180',
                'latitude_lokasi.between' => 'Latitude harus berada antara -90 sampai 90',
            ];


            $this->validate([
                'nama_lokasi' => ['required', 'string', 'min:3', 'max:100'],
                'latitude_lokasi' => ['required', 'numeric', 'between:-90,90'],
                'longitude_lokasi' => ['required', 'numeric', 'between:-180,180'],
                'radius_meter' => ['required', 'numeric']
            ], $message);

            LokasiAbsensi::create([
                'nama_lokasi' => $this->nama_lokasi,
                'latitude_lokasi' => $this->latitude_lokasi,
                'longitude_lokasi' => $this->longitude_lokasi,
                'radius_meter' => $this->radius_meter

            ]);

            LivewireAlert::title('Berhasil')
                ->text('Kamu berhasil tambah lokasi')
                ->success()
                ->toast()
                ->position('top-end')
                ->timer(3000)
                ->show();

            $this->reset(['nama_lokasi', 'latitude_lokasi', 'longitude_lokasi', 'radius_meter']);

            $this->dispatch('success')->to('admin.lokasi.index');
            // dd('event dikirim');

            // $this->dispatch('sweet-alert', icon: 'success', title: 'Kamu berhasil mendaftarkan akun baru');
        }
    };
    ?>

    <div
        wire:ignore.self
        class="modal fade"
        id="tambahLokasiModal"
        tabindex="-1"
        aria-labelledby="tambahLokasiModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tambahLokasiModalLabel">
                        Tambah Lokasi
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">
                            Nama Lokasi
                        </label>

                        <input
                            type="text"
                            wire:model="nama_lokasi"
                            class="form-control"
                            placeholder="Masukkan nama lokasi">

                        @error('nama_lokasi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Latitude
                        </label>

                        <input
                            type="number"
                            wire:model="latitude_lokasi"
                            class="form-control"
                            placeholder="Masukkan latitude lokasi">

                        @error('latitude_lokasi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Longitude
                        </label>

                        <input
                            type="number"
                            wire:model="longitude_lokasi"
                            class="form-control"
                            placeholder="Masukkan longitude lokasi">

                        @error('longitude_lokasi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Radius
                        </label>

                        <input
                            type="number"
                            wire:model="radius_meter"
                            class="form-control"
                            placeholder="Masukkan radius lokasi">

                        @error('radius_meter')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button
                        type="button"
                        wire:click="daftarin"
                        class="btn btn-primary">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>