<?php

use App\Models\DailyReport;
use App\Jobs\ExportDailyReportJob;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component
{
    use WithPagination;

    public string $search   = '';
    public ?string $tanggalAwal  = null;
    public ?string $tanggalAkhir = null;
    public int $perPage     = 20;
    public bool $isExporting = false;
    public bool $exportDone  = false;

    protected $paginationTheme = 'bootstrap';

    public function updatedTanggalAwal(): void
    {
        if ($this->tanggalAkhir && $this->tanggalAkhir < $this->tanggalAwal) {
            $this->tanggalAkhir = null;
        }
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilter(): void
    {
        $this->tanggalAwal  = null;
        $this->tanggalAkhir = null;
        $this->search       = '';
        $this->resetPage();
    }

    public function exportZip(): void
    {
        $this->isExporting = true;
        $this->exportDone  = false;

        cache()->forget('export_done');

        ExportDailyReportJob::dispatch(
            $this->tanggalAwal,
            $this->tanggalAkhir,
        );
    }

    public function checkExport(): void
    {
        if (! $this->isExporting) return;

        if (cache()->get('export_done')) {
            $this->isExporting = false;
            $this->exportDone  = true;
            cache()->forget('export_done');

            LivewireAlert::title('Berhasil Export Daily Report')
                ->success()
                ->timer(10000)
                ->toast()
                ->position('top-end')
                ->timerProgressBar()
                ->show();
        }
    }

    public function render()
    {
        $dailyReports = DailyReport::with('user.role')
            ->whereHas('user')
            ->when($this->search, fn($q) => $q->whereHas(
                'user',
                fn($u) =>
                $u->where('nama_karyawan', 'like', "%{$this->search}%")
            ))
            ->when($this->tanggalAwal,  fn($q) => $q->whereDate('created_at', '>=', $this->tanggalAwal))
            ->when($this->tanggalAkhir, fn($q) => $q->whereDate('created_at', '<=', $this->tanggalAkhir))
            ->latest()
            ->paginate($this->perPage);

        return $this->view([
            'dailyReports' => $dailyReports,
        ])
            ->layout('layouts.main')
            ->title('Daily Report');
    }
};
?>

<div class="container-fluid">
    @if($isExporting)
    <div wire:poll.3s="checkExport"></div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Daily Report</h5>

            {{-- Filter --}}
            <div class="row align-items-end g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Awal</label>
                    <input type="date" wire:model.live="tanggalAwal" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Akhir</label>
                    <input type="date" wire:model.live="tanggalAkhir" class="form-control"
                        min="{{ $tanggalAwal }}"
                        @if(!$tanggalAwal) disabled @endif>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cari Nama</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                        class="form-control" placeholder="Cari nama karyawan...">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="button" wire:click="resetFilter"
                        class="btn btn-outline-secondary w-100">
                        Reset
                    </button>

                    @if(!$isExporting)
                    <button type="button" wire:click="exportZip"
                        class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-1">
                        <i class="ti ti-file-zip"></i> Export ZIP
                    </button>
                    @else
                    <button type="button" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-1" disabled>
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Memproses...
                    </button>
                    @endif
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-sm">
                    <thead class="table-light text-uppercase text-xs">
                        <tr>
                            <th class="text-center" style="width:50px">#</th>
                            <th>Tanggal Upload</th>
                            <th>Nama Karyawan</th>
                            <th>Role</th>
                            <th class="text-center">Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailyReports as $i => $report)
                        <tr>
                            <td class="text-center text-muted">
                                {{ $dailyReports->firstItem() + $i }}
                            </td>
                            <td>
                                {{ $report->created_at->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="fw-semibold">
                                {{ ucwords($report->user->nama_karyawan ?? '-') }}
                            </td>
                            <td class="text-muted">
                                {{ ucwords($report->user->role->nama_role ?? '-') }}
                            </td>
                            <td class="text-center">
                                <button type="button"
                                    onclick="showModal('modalDoc{{ $report->daily_report_id }}')"
                                    class="btn btn-info btn-sm d-inline-flex align-items-center gap-1">
                                    <i class="ti ti-eye"></i> Lihat
                                </button>
                                <a href="{{ asset('storage/' . $report->path_dokumen) }}"
                                    download
                                    class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                    <i class="ti ti-download"></i> Unduh
                                </a>
                            </td>
                        </tr>

                        {{-- Modal Dokumen --}}
                        <div class="modal fade" id="modalDoc{{ $report->daily_report_id }}"
                            tabindex="-1" aria-hidden="true" wire:ignore.self>
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Daily Report —
                                            <strong>{{ ucwords($report->user->nama_karyawan ?? '-') }}</strong>
                                            <span class="text-muted fw-normal fs-6 ms-1">
                                                {{ $report->created_at->translatedFormat('d F Y') }}
                                            </span>
                                        </h5>
                                        <button type="button" class="btn-close"
                                            onclick="hideModal('modalDoc{{ $report->daily_report_id }}')"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <iframe
                                            src="{{ asset('storage/' . $report->path_dokumen) }}"
                                            width="100%"
                                            height="600"
                                            style="border:none">
                                        </iframe>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="{{ asset('storage/' . $report->path_dokumen) }}"
                                            download
                                            class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                            <i class="ti ti-download"></i> Unduh
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="hideModal('modalDoc{{ $report->daily_report_id }}')">
                                            Tutup
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted fst-italic py-4">
                                Tidak ada data daily report.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Per Page & Pagination --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mt-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 fw-semibold text-sm">Per Page</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width:80px">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="text-muted text-sm">
                    Menampilkan {{ $dailyReports->firstItem() ?? 0 }}–{{ $dailyReports->lastItem() ?? 0 }}
                    dari {{ $dailyReports->total() }} data
                </div>

                <div>
                    {{ $dailyReports->links('livewire::bootstrap') }}
                </div>
            </div>
        </div>
    </div>

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
</div>