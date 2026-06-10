<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Izin Absen');
    }
};
?>

<div>
    <div class="card-header" style="background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:0.5px solid var(--color-border-tertiary);margin-bottom:1rem;display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;border-radius:var(--border-radius-md);background:#E6F1FB;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-license" style="font-size:20px;color:#185FA5;"></i>
        </div>
        <div>
            <h2 style="font-size:16px;font-weight:500;color:var(--color-text-primary);">Pengajuan Izin Absensi</h2>
        </div>
    </div>
    <livewire:karyawan.izin-absen.pengajuan-izin />


    <div class="divider"></div>
    <div class="divider"></div>
    <div class="card-header" style="background:var(--color-background-primary);border-radius:var(--border-radius-lg);border:0.5px solid var(--color-border-tertiary);margin-bottom:1rem;display:flex;align-items:center;gap:10px;">
        <div style="width:36px;height:36px;border-radius:var(--border-radius-md);background:#E6F1FB;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-license" style="font-size:20px;color:#185FA5;"></i>
        </div>
        <div>
            <h2 style="font-size:16px;font-weight:500;color:var(--color-text-primary);">Riwayat Pengajuan Izin Absensi</h2>
        </div>
    </div>
    <livewire:karyawan.izin-absen.riwayat-pengajuan-izin />
</div>