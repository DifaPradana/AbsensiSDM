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
        $this->validate([
            'status' => 'required|in:menunggu konfirmasi,disetujui,ditolak'
        ], [
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status tidak valid',
        ]);

        $izin = IzinAbsen::with('user.role')->find($this->izin_id);

        if (!$izin) {
            LivewireAlert::title('Izin tidak ditemukan')->error()->show();
            return;
        }

        if ($this->status === 'disetujui') {
            $mulai  = Carbon::parse($izin->mulai_izin);
            $akhir  = Carbon::parse($izin->akhir_izin ?? $izin->mulai_izin);
            $user   = $izin->user;
            $role   = $user->role;

            $tanggal = $mulai->copy();
            while ($tanggal->lte($akhir)) {
                $sudahAda = Absensi::where('user_id', $user->user_id)
                    ->whereDate('waktu_absen_masuk', $tanggal)
                    ->exists();

                if (!$sudahAda) {
                    Absensi::create([
                        'user_id'               => $user->user_id,
                        'tipe_absensi'          => $izin->tipe_izin,
                        'waktu_absen_masuk'     => $tanggal->copy()->setTimeFromTimeString($role->jadwal_masuk),
                        'waktu_absen_pulang'    => $tanggal->copy()->setTimeFromTimeString($role->jadwal_pulang),
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
        }

        $izin->update(['status' => $this->status]);

        $this->dispatch('hide-edit-modal');
        $this->reset();
        $this->dispatch('success');

        LivewireAlert::title('Status izin berhasil diperbarui')
            ->success()
            ->timer(2500)
            ->toast()
            ->position('top-end')
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
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="closeModal">
                                Batal
                            </button>
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="izinUpdate"
                                class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <wire:loading.remove wire:target="izinUpdate">
                                    <i class="ti ti-check"></i>
                                </wire:loading.remove>
                                Simpan
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