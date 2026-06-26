<?php

use App\Models\Absensi;
use App\Models\IzinAbsen;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $izin_id;
    public $status;
    public $note;
    public IzinAbsen $izin;

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('open-edit-izin')]
    public function editIzin($izin_id)
    {
        $this->izin    = IzinAbsen::with('user.role')->findOrFail($izin_id);
        $this->izin_id = $this->izin->izin_id;
        $this->status  = $this->izin->status;

        $this->dispatch('show-edit-modal');
    }

    public function getStatusOptionsProperty(): array
    {
        if (!isset($this->izin) || $this->izin === null) {
            return [];
        }

        $user      = Auth::user();
        $role      = strtolower($user->role->nama_role ?? '');
        $current   = $this->izin->status ?? null;
        $tipe_izin = $this->izin->tipe_izin ?? null;

        if ($role === 'hrd' && $current === 'menunggu_hrd') {
            // Izin & sakit: HRD langsung approve tanpa ke direktur
            if (in_array($tipe_izin, ['izin', 'sakit'])) {
                return [
                    'disetujui'   => 'Setujui',
                    'ditolak_hrd' => 'Tolak',
                ];
            }

            // Selain itu (cuti, dll): teruskan ke direktur
            return [
                'menunggu_direktur' => 'Setujui → Teruskan ke Direktur',
                'ditolak_hrd'       => 'Tolak',
            ];
        }

        if ($role === 'direktur' && $current === 'menunggu_direktur') {
            return [
                'disetujui'        => 'Setujui',
                'ditolak_direktur' => 'Tolak',
            ];
        }

        return [];
    }

    public function izinUpdate()
    {
        if (!isset($this->izin) || $this->izin === null) {
            return;
        }

        $user      = Auth::user();
        $role      = strtolower($user->role->nama_role ?? '');
        $current   = $this->izin->status;
        $tipe_izin = $this->izin->tipe_izin ?? null;

        // HRD punya 2 jalur tergantung tipe izin
        $hrdAllowed = in_array($tipe_izin, ['izin', 'sakit'])
            ? ['disetujui', 'ditolak_hrd']           // langsung selesai
            : ['menunggu_direktur', 'ditolak_hrd'];   // lanjut ke direktur

        $allowedTransitions = [
            'hrd'      => ['menunggu_hrd'      => $hrdAllowed],
            'direktur' => ['menunggu_direktur' => ['disetujui', 'ditolak_direktur']],
        ];

        $allowed = $allowedTransitions[$role][$current] ?? [];

        $this->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in($allowed)],
            'note'   => 'nullable|string|max:500',
        ], [
            'status.required' => 'Status wajib dipilih',
            'status.in'       => 'Transisi status tidak diizinkan',
        ]);

        $izin = IzinAbsen::with('user.role')->findOrFail($this->izin_id);

        $updateData = ['status' => $this->status];

        if ($role === 'hrd') {
            $updateData['hrd_id']   = $user->user_id;
            $updateData['hrd_at']   = now();
            $updateData['hrd_note'] = $this->note;
        }

        if ($role === 'direktur') {
            $updateData['direktur_id']   = $user->user_id;
            $updateData['direktur_at']   = now();
            $updateData['direktur_note'] = $this->note;
        }

        if ($this->status === 'disetujui') {
            $this->buatAbsensiOtomatis($izin);
        }

        $izin->update($updateData);

        $this->dispatch('hide-edit-modal');
        $this->reset();
        $this->dispatch('success');

        $label = match ($this->status) {
            'menunggu_direktur' => 'Disetujui HRD, menunggu Direktur',
            'disetujui'         => 'Disetujui',
            'ditolak_hrd'       => 'Ditolak oleh HRD',
            'ditolak_direktur'  => 'Ditolak oleh Direktur',
            default             => 'Status diperbarui',
        };

        LivewireAlert::title($label)->success()->timer(2500)->toast()->position('top-end')->show();
    }

    private function buatAbsensiOtomatis(IzinAbsen $izin): void
    {
        $mulai   = Carbon::parse($izin->mulai_izin);
        $akhir   = Carbon::parse($izin->akhir_izin ?? $izin->mulai_izin);
        $user    = $izin->user;
        $role    = $user->role;
        $tanggal = $mulai->copy();

        while ($tanggal->lte($akhir)) {
            $existing = Absensi::where('user_id', $user->user_id)
                ->whereDate('waktu_absen_masuk', $tanggal)
                ->first();

            $data = [
                'user_id'               => $user->user_id,
                'tipe_absensi'          => $izin->tipe_izin,
                'waktu_absen_masuk'     => $tanggal->copy()->setTimeFromTimeString($role->jadwal_masuk),
                'waktu_absen_pulang'    => $tanggal->copy()->setTimeFromTimeString($role->jadwal_pulang),
                'lokasi_masuk'          => '-',
                'lokasi_pulang'         => '-',
                'latitude_masuk'        => '0.0',
                'longitude_masuk'       => '0.0',
                'latitude_pulang'       => '0.0',
                'longitude_pulang'      => '0.0',
                'status_absensi_masuk'  => '-',
                'status_absensi_pulang' => '-',
                'photo_masuk'           => $izin->dokumen_izin ?? null,
                'photo_pulang'          => $izin->dokumen_izin ?? null,
                'note_masuk'            => $izin->note ?? 'Dibuatkan otomatis dari pengajuan izin',
                'note_pulang'           => $izin->note ?? 'Dibuatkan otomatis dari pengajuan izin',
            ];

            if ($existing) {
                // Overwrite jika alpha, skip jika sudah ada absensi nyata
                if ($existing->tipe_absensi === 'alpha') {
                    $existing->update($data);
                }
            } else {
                Absensi::create($data);
            }

            $tanggal->addDay();
        }
    }
};
?>

<div wire:poll.15s>
    <div class="modal fade" id="editIzinModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><strong>Review Izin</strong></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="closeModal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    {{-- Info status saat ini --}}
                    @isset($this->izin)
                    <div class="mb-3">
                        @php
                        $badgeMap = [
                        'menunggu_hrd' => 'warning',
                        'menunggu_direktur' => 'info',
                        'disetujui' => 'success',
                        'ditolak_hrd' => 'danger',
                        'ditolak_direktur' => 'danger',
                        ];
                        $badge = $badgeMap[$this->izin->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $badge }}">
                            {{ str_replace('_', ' ', ucfirst($this->izin->status)) }}
                        </span>
                    </div>
                    @endisset

                    <form wire:submit.prevent="izinUpdate">

                        {{-- Dropdown status, opsi dinamis sesuai role --}}
                        <div class="mb-3">
                            <label class="form-label">Keputusan</label>
                            @if(count($this->statusOptions) > 0)
                            <select wire:model="status" class="form-select">
                                <option value="">-- Pilih keputusan --</option>
                                @foreach($this->statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @else
                            <p class="text-muted small">
                                Tidak ada tindakan yang tersedia untuk status ini.
                            </p>
                            @endif
                            @error('status')
                            <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Catatan opsional --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Catatan
                                <span class="text-muted small">(opsional)</span>
                            </label>
                            <textarea wire:model="note" class="form-control" rows="3"
                                placeholder="Alasan penolakan, atau catatan tambahan..."></textarea>
                            @error('note')
                            <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="modal-footer gap-2 px-0 pb-0">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal" wire:click="closeModal">
                                Batal
                            </button>
                            @if(count($this->statusOptions) > 0)
                            <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="izinUpdate"
                                class="btn btn-primary d-inline-flex align-items-center gap-1">
                                <wire:loading.remove wire:target="izinUpdate">
                                    <i class="ti ti-check"></i>
                                </wire:loading.remove>
                                Simpan
                            </button>
                            @endif
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