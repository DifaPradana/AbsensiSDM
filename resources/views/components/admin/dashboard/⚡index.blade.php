<?php

use App\Models\IzinAbsen;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Dashboard');
    }

    public function with(): array
    {
        $startOfMonth = now()->startOfMonth();

        return [
            'totalWaiting' => IzinAbsen::query()
                ->where('status', 'menunggu konfirmasi')
                ->where('created_at', '>=', now()->subDays(3))
                ->count(),
            'totalApproved' => IzinAbsen::query()
                ->where('status', 'disetujui')
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
            'totalRejected' => IzinAbsen::query()
                ->where('status', 'ditolak')
                ->where('created_at', '>=', $startOfMonth)
                ->count(),
        ];
    }
};
?>


<div>
    @push('title')
    <title>Dashboard </title>
    @endpush

    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <i class="ti ti-clipboard-list fs-5 text-muted"></i>
                    <h5 class="card-title fw-semibold mb-0">Pengajuan Izin</h5>
                </div>

                <div class="row g-3 mb-3">

                    {{-- Menunggu Konfirmasi --}}
                    <div class="col-md-4">
                        <a
                            class="text-decoration-none d-block p-3 rounded-3 border border-warning-subtle bg-warning-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #E49B0F !important;">
                            <i class="ti ti-clock-hour-4 text-warning fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-warning">{{ $totalWaiting }}</div>
                            <div class="small text-muted">Menunggu Konfirmasi</div>
                            <span class="badge bg-warning-subtle text-warning-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>3 hari terakhir
                            </span>
                        </a>
                    </div>

                    {{-- Disetujui --}}
                    <div class="col-md-4">
                        <a
                            class="text-decoration-none d-block p-3 rounded-3 border border-success-subtle bg-success-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #3B6D11 !important;">
                            <i class="ti ti-circle-check text-success fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-success">{{ $totalApproved }}</div>
                            <div class="small text-muted">Disetujui</div>
                            <span class="badge bg-success-subtle text-success-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>Bulan ini
                            </span>
                        </a>
                    </div>

                    {{-- Ditolak --}}
                    <div class="col-md-4">
                        <a
                            class="text-decoration-none d-block p-3 rounded-3 border border-danger-subtle bg-danger-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #A32D2D !important;">
                            <i class="ti ti-circle-x text-danger fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-danger">{{ $totalRejected }}</div>
                            <div class="small text-muted">Ditolak</div>
                            <span class="badge bg-danger-subtle text-danger-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>Bulan ini
                            </span>
                        </a>
                    </div>

                </div>

                <a href="{{ route('izin.page') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                    Lihat semua pengajuan
                    <i class="ti ti-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>


    <div class="row">
        <livewire:admin.dashboard.recent-late-attendance-component />
        <livewire:admin.dashboard.recent-early-checkout-attendance-component />
        <livewire:admin.dashboard.recent-attendance-component />
    </div>
</div>