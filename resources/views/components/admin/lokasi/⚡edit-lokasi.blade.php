<?php

use App\Models\LokasiAbsensi;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $lokasi_id;
    public $nama_lokasi;
    public $latitude_lokasi;
    public $longitude_lokasi;
    public $radius_meter;

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }


    #[On('open-edit-lokasi')]
    public function editAkun($lokasi_id)
    {
        $lokasi = LokasiAbsensi::find($lokasi_id);

        $this->lokasi_id = $lokasi->lokasi_id;
        $this->nama_lokasi = $lokasi->nama_lokasi;
        $this->latitude_lokasi = $lokasi->latitude_lokasi;
        $this->longitude_lokasi = $lokasi->longitude_lokasi;
        $this->radius_meter = $lokasi->radius_meter;


        $this->dispatch('show-edit-modal');
    }

    public function save()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }


    public function lokasiUpdate()
    {
        $message = [
            'nama_lokasi.string' => 'Nama lokasi harus berupa teks',
            'nama_lokasi.required' => 'Nama lokasi wajib diisi',
            'nama_lokasi.min' => 'Nama lokasi minimal 3 karakter',
            'nama_lokasi.max' => 'Nama lokasi maksimal 100 karakter',
            'latitude_lokasi.required' => 'Latitude wajib diisi',
            'latitude_lokasi.max' => 'Latitude maksimal 10 digit',
            'latitude_lokasi.numeric' => 'Latitude harus berupa angka',
            'latitude_lokasi.between' => 'Latitude harus berada di antara -90 sampai 90',
            'longitude_lokasi.required' => 'Longitude wajib diisi',
            'longitude_lokasi.max' => 'Longitude maksimal 10 digit',
            'longitude_lokasi.numeric' => 'Longitude harus berupa angka',
            'longitude_lokasi.between' => 'Longitude harus berada di antara -180 sampai 180',
            'radius_meter.required' => 'Radius wajib diisi',
            'radius_meter.integer' => 'Radius harus berupa angka bulat',
            'radius_meter.min' => 'Radius minimal 1 meter',
        ];


        $this->validate([
            'nama_lokasi' => 'required|string|min:3|max:100',
            'latitude_lokasi' => 'required|numeric|between:-90,90',
            'longitude_lokasi' => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:1|max:10000',
        ], $message);

        $lokasi = LokasiAbsensi::find($this->lokasi_id);
        // dd($this->status);
        if (!$lokasi) {
            LivewireAlert::title('Error')
                ->text('Data tidak ditemukan')
                ->error()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();
            return;
        }

        if ($lokasi) {
            $lokasi->update([
                'nama_lokasi' => $this->nama_lokasi,
                'latitude_lokasi' => $this->latitude_lokasi,
                'longitude_lokasi' => $this->longitude_lokasi,
                'radius_meter' => $this->radius_meter
            ]);


            $this->dispatch('hide-edit-modal');
            $this->reset();
            $this->dispatch('success');
            LivewireAlert::title('Berhasil Edit')
                ->text('Berhasil edit lokasi')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();
        }
    }
};
?>

<div>
    <div class="modal fade" id="editLokasiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLokasiModal">
                        <strong>Edit Akun</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closeModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="lokasiUpdate">
                        <div class=" mb-3">
                            <label for="exampleInputNamaKaryawan1" class="form-label">Nama Karyawan</label>
                            <input wire:model="nama_lokasi" type="text" class="form-control" id="nama_lokasi"
                                aria-describedby="NamaKaryawanHelp">
                            @error('nama_lokasi')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                Latitude
                            </label>

                            <input
                                type="text"
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
                                type="text"
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

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                wire:click="closeModal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    Livewire.on('hide-edit-modal', () => {
        const modal = bootstrap.Modal.getInstance(
            document.getElementById('editLokasiModal')
        );

        modal?.hide();
    });
</script>