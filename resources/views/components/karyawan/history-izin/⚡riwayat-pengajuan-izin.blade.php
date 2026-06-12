<?php

use App\Models\IzinAbsen;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function delete($izin)
    {
        $izin = IzinAbsen::where('izin_id', $izin)->firstOrFail();
        // dd($izin->dokumen_izin);
        Storage::disk('public')->delete($izin->dokumen_izin);
        $izin->delete();
        LivewireAlert::title('Berhasil')
            ->text('Kamu berhasil delete izin')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function with(): array
    {
        return [
            'izins' => IzinAbsen::query()
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
                $totalWaiting = $izins->getCollection()->where('status', 'menunggu konfirmasi')->count();
                $totalAcc = $izins->getCollection()->where('status', 'disetujui')->count();
                $totalCancel = $izins->getCollection()->where('status', 'ditolak')->count();
                @endphp

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="alert alert-warning text-center mb-0">
                            <div class="fw-semibold fs-5">{{ $totalWaiting }}</div>
                            <div class="small">Menunggu Konfirmasi</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-success text-center mb-0">
                            <div class="fw-semibold fs-5">{{ $totalAcc }}</div>
                            <div class="small">Disetujui</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="alert alert-danger text-center mb-0">
                            <div class="fw-semibold fs-5">{{ $totalCancel }}</div>
                            <div class="small">Ditolak</div>
                        </div>
                    </div>
                </div>

                <div class="abs-grid">
                    @forelse ($izins as $izin)

                    @php
                    $badgeClass = match($izin->status) {
                    'menunggu konfirmasi' => 'bg-warning',
                    'batal' => 'bg-danger',
                    'disetujui' => 'bg-success',
                    default => 'bg-secondary'
                    };

                    $durasi = $izin->akhir_izin
                    ? \Carbon\Carbon::parse($izin->mulai_izin)
                    ->diffInDays($izin->akhir_izin) + 1
                    : 1;
                    @endphp

                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">

                            <div>
                                <i class="ti ti-calendar"></i>
                                {{ $izin->created_at->translatedFormat('d F Y') }}
                            </div>

                            <span class="badge {{ $badgeClass }}">
                                {{ ucfirst($izin->status) }}
                            </span>

                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Tipe Izin</small>
                                    <div class="fw-semibold">
                                        {{ ucwords($izin->tipe_izin ?? '-') }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Karyawan</small>
                                    <div class="fw-semibold">
                                        {{ ucwords($izin->user->nama_karyawan ?? '-') }}
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
                                        {{ \Carbon\Carbon::parse($izin->mulai_izin)->translatedFormat('d F Y') }}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Akhir Izin</small>
                                    <div>
                                        {{ $izin->akhir_izin
                                ? \Carbon\Carbon::parse($izin->akhir_izin)->translatedFormat('d F Y')
                                : '-' }}
                                    </div>
                                </div>

                            </div>
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                @if($izin->dokumen_izin)
                                <div>

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        onclick="showModal('modalDokumen{{ $izin->izin_id }}')">

                                        <i class=" ti ti-eye"></i>
                                        Lihat Dokumen

                                    </button>
                                </div>
                                @endif
                                <div>
                                    <button
                                        onclick="confirm('Kamu akan menghapus izin secara permanen, apakah yakin?') || event.stopImmediatePropagation()"
                                        wire:click="delete({{ $izin->izin_id }})"
                                        class="btn btn-danger btn-sm">
                                        <i class="ti ti-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Dokumen --}}
                    @if($izin->dokumen_izin)

                    <div
                        class="modal fade"
                        id="modalDokumen{{ $izin->izin_id }}"
                        tabindex="-1"
                        aria-hidden="true"
                        wire:ignore.self>

                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        Dokumen {{ ucfirst($izin->tipe_izin) }}
                                    </h5>

                                    <button
                                        type="button"
                                        class="btn-close"
                                        onclick="hideModal('modalDokumen{{ $izin->izin_id }}')">
                                    </button>
                                </div>

                                <div class="modal-body text-center">

                                    @php
                                    $ext = strtolower(pathinfo($izin->dokumen_izin, PATHINFO_EXTENSION));
                                    @endphp

                                    @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                    <img
                                        src="{{ asset('storage/'.$izin->dokumen_izin) }}"
                                        class="img-fluid rounded">
                                    @elseif($ext === 'pdf')
                                    <iframe
                                        src="{{ asset('storage/'.$izin->dokumen_izin) }}"
                                        width="100%"
                                        height="600">
                                    </iframe>
                                    @else
                                    <a
                                        href="{{ asset('storage/'.$izin->dokumen_izin) }}"
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
                                        onclick="hideModal('modalDokumen{{ $izin->izin_id }}')">

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
                    Menampilkan {{ $izins->firstItem() ?? 0 }}–{{ $izins->lastItem() ?? 0 }}
                    dari {{ $izins->total() }} data
                </div>
            </div>
            <div class="mt-3">
                {{ $izins->links() }}
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