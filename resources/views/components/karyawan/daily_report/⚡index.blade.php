<?php

use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Daily Report');
    }

    #[On('daily-report-success')]
    public function getStatusDailyReportProperty()
    {
        $user = Auth::user();

        $todayDailyReport = DailyReport::where('user_id', $user->user_id)
            ->whereDate('created_at', today())
            ->exists();

        if (!$todayDailyReport) {
            return 'Upload Daily Report';
        }

        return 'Sudah Upload Daily Report';
    }
};
?>

<div>
    <div class="card-header" style="background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:0.5px solid var(--color-border-tertiary);margin-bottom:1rem;display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;border-radius:var(--border-radius-md);background:#E6F1FB;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-fingerprint" style="font-size:20px;color:#185FA5;"></i>
        </div>
        <div>
            <h2 style="font-size:16px;font-weight:500;color:var(--color-text-primary);">{{ $this->statusDailyReport }}</h2>
        </div>
    </div>

    <div id="alert-box" style="display:none;margin-bottom:1rem;" class="alert"></div>
    @if ($this->statusDailyReport == "Sudah Upload Daily Report")
    <div class="card">
        <div class="card-body">
            <h2 style="font-size:16px;font-weight:500;color:var(--color-text-primary);">Sudah Upload Daily Report</h2>
        </div>
    </div>
    @else
    <livewire:karyawan.daily_report.post-daily-report />
    @endif

</div>