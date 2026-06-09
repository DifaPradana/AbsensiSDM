<?php

use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{


    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Absensi Karyawan');
    }


    #[On('absen-success')]
    public function getStatusPresensiProperty()
    {
        $user = Auth::user();

        $absenDatang = Absensi::where('user_id', $user->user_id)
            ->whereDate('waktu_absen_masuk', today())
            ->exists();

        $absenPulang = Absensi::where('user_id', $user->user_id)
            ->whereDate('waktu_absen_pulang', today())
            ->exists();

        if (!$absenDatang) {
            return 'Presensi Masuk';
        }

        if (!$absenPulang) {
            return 'Presensi Pulang';
        }

        return 'Presensi Hari Ini Selesai';
    }
};
?>

<div>
    <div class="wrap">
        <div class="card-header" style="background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:0.5px solid var(--color-border-tertiary);margin-bottom:1rem;display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:var(--border-radius-md);background:#E6F1FB;display:flex;align-items:center;justify-content:center;">
                <i class="ti ti-fingerprint" style="font-size:20px;color:#185FA5;"></i>
            </div>
            <div>
                <h2 style="font-size:16px;font-weight:500;color:var(--color-text-primary);">{{ $this->statusPresensi }}</h2>
            </div>
        </div>

        <div id="alert-box" style="display:none;margin-bottom:1rem;" class="alert"></div>
        @if ($this->statusPresensi == "Presensi Hari Ini Selesai")
        <div class="card">
            <div class="card-body">
                <h2 style="font-size:16px;font-weight:500;color:var(--color-text-primary);">Absensi Hari Ini Sudah Lengkap</h2>
            </div>
        </div>
        @else
        <livewire:karyawan.absensi.post-absen />
        @endif

    </div>
</div>