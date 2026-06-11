<?php

use App\Models\IzinAbsen;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'absensis' => IzinAbsen::query()
                ->with('user')
                ->latest('created_at')
                ->paginate($this->perPage),
        ];
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            {{-- Card List --}}
            <div class="abs-grid">
                {{-- Summary Badge --}}
                @php
                $izin = $absensis->getCollection()->where('tipe_izin', 'izin')->count();
                $sakit = $absensis->getCollection()->where('tipe_izin', 'sakit')->count();
                $cuti = $absensis->getCollection()->where('tipe_izin', 'cuti')->count();
                @endphp

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="alert alert-warning text-center mb-0">
                            <div class="fw-semibold fs-5">{{ $izin }}</div>
                            <div class="small">Izin</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-info text-center mb-0">
                            <div class="fw-semibold fs-5">{{ $sakit }}</div>
                            <div class="small">Sakit</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-success text-center mb-0">
                            <div class="fw-semibold fs-5">{{ $cuti }}</div>
                            <div class="small">Cuti</div>
                        </div>
                    </div>
                </div>

                <div class="abs-grid">
                    @forelse ($absensis as $absensi)

                    @php
                    $badgeClass = match($absensi->status) {
                    'menunggu konfirmasi' => 'bg-warning',
                    'batal' => 'bg-danger',
                    'terkonfirmasi' => 'bg-success',
                    default => 'bg-secondary'
                    };

                    $durasi = $absensi->akhir_izin
                    ? \Carbon\Carbon::parse($absensi->mulai_izin)
                    ->diffInDays($absensi->akhir_izin) + 1
                    : 1;
                    @endphp

                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">

                            <div>
                                <i class="ti ti-calendar"></i>
                                {{ $absensi->created_at->translatedFormat('d F Y') }}
                            </div>

                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst($absensi->status) }}
                            </span>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Tipe Izin</small>
                                    <div class="fw-semibold">
                                        {{ ucwords($absensi->tipe_izin ?? '-') }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Karyawan</small>
                                    <div class="fw-semibold">
                                        {{ ucwords($absensi->user->nama_karyawan ?? '-') }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Durasi</small>
                                    <div class="fw-semibold">
                                        {{ $durasi }} Hari
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Mulai Izin</small>
                                    <div>
                                        {{ \Carbon\Carbon::parse($absensi->mulai_izin)->translatedFormat('d F Y') }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Akhir Izin</small>
                                    <div>
                                        {{ $absensi->akhir_izin
                                ? \Carbon\Carbon::parse($absensi->akhir_izin)->translatedFormat('d F Y')
                                : '-' }}
                                    </div>
                                </div>

                            </div>

                            @if($absensi->dokumen_izin)
                            <div class="mt-2">

                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    onclick="showModal('modalDokumen{{ $absensi->id_izin }}')">

                                    <i class="ti ti-eye"></i>
                                    Lihat Dokumen

                                </button>

                            </div>
                            @endif

                        </div>
                    </div>

                    {{-- Modal Dokumen --}}
                    @if($absensi->dokumen_izin)

                    <div
                        class="modal fade"
                        id="modalDokumen{{ $absensi->id_izin }}"
                        tabindex="-1"
                        aria-hidden="true"
                        wire:ignore.self>

                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Dokumen {{ ucfirst($absensi->tipe_izin) }}
                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        onclick="hideModal('modalDokumen{{ $absensi->id_izin }}')">
                                    </button>
                                </div>

                                <div class="modal-body text-center">

                                    @php
                                    $ext = strtolower(pathinfo($absensi->dokumen_izin, PATHINFO_EXTENSION));
                                    @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                    <img
                                        src="{{ asset('storage/'.$absensi->dokumen_izin) }}"
                                        class="img-fluid rounded">
                                    @elseif($ext === 'pdf')
                                    <iframe
                                        src="{{ asset('storage/'.$absensi->dokumen_izin) }}"
                                        width="100%"
                                        height="600">
                                    </iframe>
                                    @else
                                    <a
                                        href="{{ asset('storage/'.$absensi->dokumen_izin) }}"
                                        target="_blank"
                                        class="btn btn-primary">

                                        Download Dokumen

                                    </a>
                                    @endif

                                </div>

                                <div class="modal-footer">
                                    <button
                                        type="button"
                                        class="btn btn-secondary"
                                        onclick="hideModal('modalDokumen{{ $absensi->id_izin }}')">

                                        Tutup

                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    @endif

                    @empty

                    <div class="alert alert-light text-center">
                        Tidak ada data izin.
                    </div>

                    @endforelse
                </div>
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