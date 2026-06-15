<?php

use App\Models\Absensi;
use Livewire\Component;

new class extends Component
{
    public $search = '';
    public $tanggalAwal;
    public $tanggalAkhir;
    public $perPage = 20;

    public function updatedTanggalAwal($value)
    {
        if ($this->tanggalAkhir && $this->tanggalAkhir < $value) {
            $this->tanggalAkhir = null;
        }
    }

    public function resetFilter()
    {
        $this->tanggalAwal = null;
        $this->tanggalAkhir = null;
        $this->search = '';
    }

    public function render()
    {
        return $this->view([
            'absensis' => Absensi::with('user')
                ->whereHas('user', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->search($this->search)
                ->filterTanggal($this->tanggalAwal, $this->tanggalAkhir)
                ->latest()
                ->paginate($this->perPage),
        ])
            ->layout('layouts.main')
            ->title('Absensi | Account');
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Riwayat Kehadiran</h5>

            {{-- Filter Bar --}}
            <div class="row align-items-end g-3 mb-4">
                <div class="col-md-4">
                    <label for="tanggalAwal" class="form-label fw-semibold">Tanggal Awal</label>
                    <input type="date" id="tanggalAwal" wire:model.live="tanggalAwal" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="tanggalAkhir" class="form-label fw-semibold">Tanggal Akhir</label>
                    <input type="date" id="tanggalAkhir" wire:model.live="tanggalAkhir" class="form-control"
                        min="{{ $tanggalAwal }}"
                        @if(!$tanggalAwal) disabled @endif>
                </div>
                <div class="col-md-2">
                    <button type="button" wire:click="resetFilter" class="btn btn-outline-secondary w-100">
                        Reset
                    </button>
                </div>
            </div>

            {{-- Summary Badge --}}
            @php
            $total = $absensis->total();
            $hadir = $absensis->getCollection()->where('tipe_absensi', 'hadir')->count();
            $izin = $absensis->getCollection()->where('tipe_absensi', 'izin')->count();
            $sakit = $absensis->getCollection()->where('tipe_absensi', 'sakit')->count();
            $alfa = $absensis->getCollection()->where('tipe_absensi', 'alfa')->count();
            @endphp
            <div class="row g-2 mb-3">
                <div class="col-6 col-md-3">
                    <div class="alert alert-success mb-0 py-2 text-center" role="alert">
                        <div class="fw-semibold fs-5">{{ $hadir }}</div>
                        <div class="small">Hadir</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="alert alert-warning mb-0 py-2 text-center" role="alert">
                        <div class="fw-semibold fs-5">{{ $izin }}</div>
                        <div class="small">Izin</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="alert alert-info mb-0 py-2 text-center" role="alert">
                        <div class="fw-semibold fs-5">{{ $sakit }}</div>
                        <div class="small">Sakit</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="alert alert-danger mb-0 py-2 text-center" role="alert">
                        <div class="fw-semibold fs-5">{{ $alfa }}</div>
                        <div class="small">Alfa</div>
                    </div>
                </div>
            </div>

            {{-- Card List --}}
            <div class="abs-grid">
                @forelse ($absensis as $absensi)
                @php
                $tipe = strtolower($absensi->tipe_absensi ?? '-');
                $tipeBadgeClass = match($tipe) {
                'hadir' => 'abs-badge-hadir',
                'izin' => 'abs-badge-izin',
                'sakit' => 'abs-badge-sakit',
                'alfa' => 'abs-badge-alfa',
                default => 'abs-badge-default',
                };

                $statusMasuk = $absensi->status_absensi_masuk ?? '';
                $statusMasukClass = str_starts_with($statusMasuk, 'Terlambat') ? 'abs-badge-terlambat' : 'abs-badge-tepat';

                $statusPulang = $absensi->status_absensi_pulang ?? '';
                $statusPulangClass = match(true) {
                str_starts_with($statusPulang, 'Terlambat') => 'abs-badge-terlambat',
                str_starts_with($statusPulang, 'Pulang Lebih Cepat') => 'abs-badge-cepat',
                empty($statusPulang) => '',
                default => 'abs-badge-tepat',
                };
                @endphp

                <div class="abs-card">
                    {{-- Card Header --}}
                    <div class="abs-card-header">
                        <div class="abs-date">
                            <i class="ti ti-calendar" aria-hidden="true"></i>
                            {{ $absensi->created_at ? $absensi->created_at->translatedFormat('l, d F Y') : '-' }}
                        </div>
                        <span class="abs-badge {{ $tipeBadgeClass }}">
                            {{ ucfirst($absensi->tipe_absensi ?? '-') }}
                        </span>
                    </div>

                    {{-- Card Body --}}
                    <div class="abs-body">

                        {{-- Kolom Masuk --}}
                        <div class="abs-section">
                            <div class="abs-section-title">
                                <i class="ti ti-login" aria-hidden="true"></i> Masuk
                            </div>

                            <div class="abs-row">
                                <span class="abs-label">Waktu</span>
                                <span class="abs-val">
                                    @if($statusMasuk)
                                    <span class="abs-badge {{ $statusMasukClass }}">{{ $statusMasuk }}</span>
                                    @else
                                    <span class="abs-muted">—</span>
                                    @endif
                                </span>
                            </div>

                            <div class="abs-row">
                                <span class="abs-label">Lokasi</span>
                                <span class="abs-val">
                                    @if($absensi->lokasi_masuk === 'Unknown' && $absensi->latitude_masuk && $absensi->longitude_masuk)
                                    <a href="https://www.google.com/maps?q={{ $absensi->latitude_masuk }},{{ $absensi->longitude_masuk }}"
                                        target="_blank" class="abs-link">
                                        {{ $absensi->latitude_masuk }}, {{ $absensi->longitude_masuk }}
                                    </a>
                                    @else
                                    {{ $absensi->lokasi_masuk ?? '-' }}
                                    @endif
                                </span>
                            </div>

                            @if($absensi->note_masuk)
                            <div class="abs-row">
                                <span class="abs-label">Catatan</span>
                                <span class="abs-val abs-note">{{ $absensi->note_masuk }}</span>
                            </div>
                            @endif

                            @if($absensi->photo_masuk)
                            <div class="abs-row">
                                <span class="abs-label">Foto</span>
                                <button type="button"
                                    onclick="showModal('modalMasuk{{ $absensi->absensi_id }}')"
                                    class="abs-photo-btn">
                                    <i class="ti ti-eye" aria-hidden="true"></i> Lihat
                                </button>
                            </div>
                            @endif
                        </div>

                        {{-- Kolom Pulang --}}
                        <div class="abs-section">
                            <div class="abs-section-title">
                                <i class="ti ti-logout" aria-hidden="true"></i> Pulang
                            </div>

                            <div class="abs-row">
                                <span class="abs-label">Waktu</span>
                                <span class="abs-val">
                                    @if($statusPulang)
                                    <span class="abs-badge {{ $statusPulangClass }}">{{ $statusPulang }}</span>
                                    @else
                                    <span class="abs-muted">—</span>
                                    @endif
                                </span>
                            </div>

                            <div class="abs-row">
                                <span class="abs-label">Lokasi</span>
                                <span class="abs-val">
                                    @if($absensi->lokasi_pulang === 'Unknown' && $absensi->latitude_pulang && $absensi->longitude_pulang)
                                    <a href="https://www.google.com/maps?q={{ $absensi->latitude_pulang }},{{ $absensi->longitude_pulang }}"
                                        target="_blank" class="abs-link">
                                        {{ $absensi->latitude_pulang }}, {{ $absensi->longitude_pulang }}
                                    </a>
                                    @else
                                    {{ $absensi->lokasi_pulang ?? '-' }}
                                    @endif
                                </span>
                            </div>

                            @if($absensi->note_pulang)
                            <div class="abs-row">
                                <span class="abs-label">Catatan</span>
                                <span class="abs-val abs-note">{{ $absensi->note_pulang }}</span>
                            </div>
                            @endif

                            @if($absensi->photo_pulang)
                            <div class="abs-row">
                                <span class="abs-label">Foto</span>
                                <button type="button"
                                    onclick="showModal('modalPulang{{ $absensi->absensi_id }}')"
                                    class="abs-photo-btn">
                                    <i class="ti ti-eye" aria-hidden="true"></i> Lihat
                                </button>
                            </div>
                            @endif
                        </div>

                    </div>{{-- end abs-body --}}
                </div>{{-- end abs-card --}}

                {{-- Modal Foto Masuk --}}
                <div class="modal fade" id="modalMasuk{{ $absensi->absensi_id }}" tabindex="-1" aria-hidden="true" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Foto Masuk — <strong>{{ $absensi->user->nama_karyawan ?? '-' }}</strong></h5>
                                <button type="button" class="btn-close" onclick="hideModal('modalMasuk{{ $absensi->absensi_id }}')"></button>
                            </div>
                            <div class="modal-body text-center">
                                @if ($absensi->photo_masuk)
                                <img src="{{ Storage::url($absensi->photo_masuk) }}">
                                @else
                                <span class="text-muted">Foto tidak tersedia</span>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="hideModal('modalMasuk{{ $absensi->absensi_id }}')">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Foto Pulang --}}
                <div class="modal fade" id="modalPulang{{ $absensi->absensi_id }}" tabindex="-1" aria-hidden="true" wire:ignore.self>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Foto Pulang — <strong>{{ $absensi->user->nama_karyawan ?? '-' }}</strong></h5>
                                <button type="button" class="btn-close" onclick="hideModal('modalPulang{{ $absensi->absensi_id }}')"></button>
                            </div>
                            <div class="modal-body text-center">
                                @if ($absensi->photo_pulang)
                                <img src="{{ Storage::url($absensi->photo_pulang) }}">
                                @else
                                <span class="text-muted">Foto tidak tersedia</span>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="hideModal('modalPulang{{ $absensi->absensi_id }}')">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

                @empty
                <div class="abs-empty">
                    <i class="ti ti-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                    Tidak ada data absensi.
                </div>
                @endforelse
            </div>{{-- end abs-grid --}}

            {{-- Per Page & Pagination --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-4">
                <div class="d-flex align-items-center gap-2">
                    <label class="text-sm fw-medium text-muted mb-0 text-nowrap">Per Page</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width:auto">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="text-muted" style="font-size:13px;">
                    Menampilkan {{ $absensis->firstItem() ?? 0 }}–{{ $absensis->lastItem() ?? 0 }}
                    dari {{ $absensis->total() }} data
                </div>
            </div>
            <div class="mt-3">
                {{ $absensis->links() }}
            </div>

        </div>{{-- end card-body --}}
    </div>{{-- end card --}}

    <script>
        function showModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            new bootstrap.Modal(el, {
                backdrop: true
            }).show();
        }

        function hideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        }

        document.addEventListener('hidden.bs.modal', function() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        });
    </script>
</div>{{-- end container-fluid --}}



{{-- ==================== SCRIPTS ==================== --}}