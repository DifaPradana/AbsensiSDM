<?php

use App\Models\IzinAbsen;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

new class extends Component
{

    use WithFileUploads;

    public $dropdownItem = [];
    public $tipe_izin;
    public $tanggalAwal;
    public $tanggalAkhir;
    public $note;
    public $photo;

    public function mount()
    {
        $this->dropdownItem = ['izin', 'sakit'];
    }

    public function izin()
    {
        $user = Auth::user();

        $validate = Validator::make([
            'tipe_izin'  => $this->tipe_izin,
            'tanggalAwal' => $this->tanggalAwal,
            'tanggalAkhir' => $this->tanggalAkhir,
            'note'      => $this->note,
            'photo'     => $this->photo,
        ], [
            'tipe_izin' => 'required|in:izin,sakit,cuti',
            'tanggalAwal' => 'required|date',
            'tanggalAkhir' => 'nullable|date|after_or_equal:tanggalAwal',
            'note' => 'nullable|string|max:500',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'tipe_izin.required'  => 'Tipe izin harus diisi',
            'tanggalAwal.required' => 'Tanggal Awal harus diisi',
            'photo.required'     => 'Dokumen harus diambil',
            'photo.image'        => 'File harus berupa gambar',
            'photo.mimes'        => 'Photo harus berupa gambar atau pdf',
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


        if ($this->photo) {

            $uploadedFile = $this->photo;

            $filename = Str::uuid() . '.' . $uploadedFile->extension();
            $relativePath = 'dokumen-izin/' . $filename;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $mimeType = $uploadedFile->getMimeType();

            if (str_starts_with($mimeType, 'image/')) {

                $manager = ImageManager::usingDriver(Driver::class);

                $image = $manager->decodeSplFileInfo($uploadedFile);

                // resize hanya jika lebih besar dari 1200px
                if ($image->width() > 1200) {
                    $image->scale(width: 1200);
                }

                $image->save($fullPath, quality: 70);
            } else {

                // PDF atau file lain langsung simpan
                copy(
                    $uploadedFile->getRealPath(),
                    $fullPath
                );
            }

            $dokumenPath = $relativePath;
        }

        IzinAbsen::create([
            'user_id' => $user->user_id,
            'tipe_izin' => $this->tipe_izin,
            'mulai_izin' => $this->tanggalAwal,
            'akhir_izin' => $this->tanggalAkhir ?: $this->tanggalAwal,
            'dokumen_izin' => $dokumenPath,
            'note' => $this->note,
        ]);

        $this->reset([
            'tipe_izin',
            'tanggalAwal',
            'tanggalAkhir',
            'photo',
            'note',
        ]);

        LivewireAlert::title('Pengajuan Izin Berhasil')
            ->text('Pengajuanmu akan di cek, sabar ya')
            ->success()
            ->timer(null)
            ->withOptions(['allowOutsideClick' => false])
            ->withConfirmButton('Ok')
            ->onConfirm('reloadPage')
            ->show();
    }

    #[On('absen-success')]
    public function reloadPage()
    {
        $this->redirectRoute('karyawan.history-izin');
    }
};
?>

<div>
    <form wire:submit.prevent="izin">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">
                        Tipe Izin
                    </label>
                    <select wire:model="tipe_izin" class="form-select">

                        <option value="" selected>
                            -- Pilih Izin --
                        </option>

                        @foreach ($dropdownItem as $item)
                        <option value="{{ $item }}">
                            {{ ucfirst($item) }}
                        </option>
                        @endforeach

                    </select>
                </div>
                <div class="divider"></div>
                <label class="form-label">
                    Foto Surat
                </label>
                <input type="file" wire:model="photo" accept="image/*, .pdf" capture="user" class="form-control" id="photo">
                <div wire:loading wire:target="photo" class="text-primary mt-2">
                    <span class="spinner-border spinner-border-sm"></span>
                    Uploading...
                </div>
                <div class="divider"></div>
                <div class="row align-items-end g-3">
                    <div class="col-12 col-md-6">
                        <label for="tanggalAwal" class="form-label fw-semibold">Tanggal Awal</label>
                        <input type="date" id="tanggalAwal" wire:model.live="tanggalAwal" class="form-control" wire:model.live="tanggalAwal">

                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tanggalAkhir" class="form-label fw-semibold">Tanggal Akhir</label>
                        <input type="date" id="tanggalAkhir" wire:model.live="tanggalAkhir" class="form-control" wire:model.live="tanggalAkhir"
                            min="{{ $tanggalAwal }}" {{-- mencegah pilih tanggal sebelum tanggalAwal --}}
                            class="form-control"
                            @if(!$tanggalAwal) disabled @endif>
                    </div>
                </div>

                <div class="divider"></div>
                <label class="form-label">
                    Catatan
                </label>
                <textarea wire:model="note" placeholder="Tambahkan catatan (opsional)..." class="form-control"></textarea>

                <div class="divider"></div>

                <button
                    type="submit"
                    class="btn btn-primary"
                    style="width:100%;justify-content:center;"
                    wire:loading.attr="disabled"
                    wire:target="photo, absen">
                    <i class="ti ti-send" style="font-size:15px;"></i>
                    <span>Kirim Pengajuan Izin</span>
                </button>
            </div>
        </div>
    </form>
</div>