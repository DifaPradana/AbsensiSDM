<?php

use App\Models\Absensi;
use App\Models\IzinAbsen;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $izin_id;
    public $status;

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('open-edit-izin')]
    public function editIzin($izin_id)
    {
        $izin = IzinAbsen::find($izin_id);
        $this->izin_id = $izin->izin_id;
        $this->status = $izin->status;

        $this->dispatch('show-edit-modal');
    }

    public function save()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }

    public function izinUpdate()
    {
        $message = [
            'status.required' => 'status wajib diisi',
            'status.in' => 'status tidak valid',
        ];

        $this->validate([
            'status' => 'required|in:menunggu konfirmasi,disetujui,ditolak'
        ], $message);

        $izin = IzinAbsen::find($this->izin_id);

        if (!$izin) {
            LivewireAlert::title('Izin tidak ditemukan')
                ->error()
                ->show();
        }

        $izin->update([
            'status' => $this->status
        ]);

        $this->dispatch('hide-edit-modal');
        $this->reset();
        $this->dispatch('success');
        LivewireAlert::title('Berhasil Izin')
            ->success()
            ->timer(3000)
            ->toast()
            ->position('top-end')
            ->show();
    }

    public function konfirmasi($izin_id, $aksi)
    {
        $izin = IzinAbsen::with('user.role')->findOrFail($izin_id);

        if ($aksi === 'ditolak') {
            $izin->update(['status' => 'ditolak']);

            $this->dispatch('close-edit-izin');
            LivewireAlert::title('Izin ditolak')->warning()->timer(2000)->toast()->show();
            return;
        }

        $mulai  = Carbon::parse($izin->mulai_izin);
        $akhir  = Carbon::parse($izin->akhir_izin ?? $izin->mulai_izin);
        $user   = $izin->user;
        $role   = $user->role; // langsung dari relasi, sama seperti absen()

        $tanggal = $mulai->copy();

        while ($tanggal->lte($akhir)) {
            $sudahAda = Absensi::where('user_id', $user->user_id)
                ->whereDate('waktu_absen_masuk', $tanggal)
                ->exists();

            if (!$sudahAda) {
                $waktuMasuk  = $tanggal->copy()->setTimeFromTimeString($role->jadwal_masuk);
                $waktuPulang = $tanggal->copy()->setTimeFromTimeString($role->jadwal_pulang);

                Absensi::create([
                    'user_id'               => $user->user_id,
                    'tipe_absensi'          => $izin->tipe_izin,
                    'waktu_absen_masuk'     => $waktuMasuk,
                    'waktu_absen_pulang'    => $waktuPulang,
                    'lokasi_masuk'          => 'Izin',
                    'lokasi_pulang'         => 'Izin',
                    'latitude_masuk'        => '0.0',
                    'longitude_masuk'       => '0.0',
                    'latitude_pulang'       => '0.0',
                    'longitude_pulang'      => '0.0',
                    'status_absensi_masuk'  => ucfirst($izin->tipe_izin),
                    'status_absensi_pulang' => ucfirst($izin->tipe_izin),
                    'photo_masuk'           => $izin->dokumen_izin ?? null,
                    'photo_pulang'          => $izin->dokumen_izin ?? null,
                    'note_masuk'            => $izin->note ?? 'Dibuatkan otomatis dari pengajuan izin',
                    'note_pulang'           => $izin->note ?? 'Dibuatkan otomatis dari pengajuan izin',
                ]);
            }

            $tanggal->addDay();
        }

        $izin->update(['status' => 'disetujui']);

        $this->dispatch('hide-edit-modal');
        $this->dispatch('success');
        LivewireAlert::title('Izin disetujui dan absensi dibuat')
            ->success()
            ->timer(2500)
            ->toast()
            ->show();
    }
};
?>

<div>
    <div class="modal fade" id="editIzinModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIzinModal">
                        <strong>Edit Izin</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closeModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="izinUpdate">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select wire:model="status" class="form-select" id="status"
                                aria-label="Default select example">
                                <option value="" selected>Status</option>
                                <option value="menunggu konfirmasi">
                                    Menunggu Konfirmasi
                                </option>
                                <option value="disetujui">
                                    Disetujui
                                </option>
                                <option value="ditolak">
                                    Ditolak
                                </option>
                            </select>
                            @error('status')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        {{-- Di dalam modal footer --}}
                        <div class="modal-footer gap-2">
                            <button
                                type="button"
                                class="btn btn-secondary"
                                wire:click="$dispatch('close-edit-izin')">
                                Batal
                            </button>

                            <button
                                type="button"
                                wire:click="konfirmasi({{ $izin_id }}, 'ditolak')"
                                wire:loading.attr="disabled"
                                wire:target="konfirmasi"
                                class="btn btn-danger d-inline-flex align-items-center gap-1">
                                <i class="ti ti-x"></i>
                                Tolak
                            </button>

                            <button
                                type="button"
                                wire:click="konfirmasi({{ $izin_id }}, 'disetujui')"
                                wire:loading.attr="disabled"
                                wire:target="konfirmasi"
                                class="btn btn-success d-inline-flex align-items-center gap-1">
                                <wire:loading wire:target="konfirmasi">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </wire:loading>
                                <wire:loading.remove wire:target="konfirmasi">
                                    <i class="ti ti-check"></i>
                                </wire:loading.remove>
                                Setujui
                            </button>
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
            document.getElementById('editIzinModal')
        );
        modal?.hide();
    });
</script>