<?php

use Livewire\Component;

new class extends Component
{
    public $dropdownItem = [];
    public $tipe_izin;
    public $tanggalAwal;
    public $tanggalAkhir;

    public function mount()
    {
        $this->dropdownItem = ['izin', 'sakit'];
    }
};
?>

<div>
    <form wire:submit.prevent="absen">
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