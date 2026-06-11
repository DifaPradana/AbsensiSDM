<?php

use App\Models\Absensi;
use Livewire\Component;

new class extends Component
{
    public function getRecentAttendance()
    {
        $absensis = Absensi::with('user')
            ->whereDate('waktu_absen_masuk', today()) // ← filter hari ini
            ->latest()
            ->get(); // ← hapus take(20) agar semua data hari ini masuk

        $masuk = [];
        $pulang = [];

        foreach ($absensis as $absen) {


            if (
                !empty($absen->status_absensi_pulang) &&
                str_starts_with($absen->status_absensi_pulang, 'Pulang Lebih Cepat')
            ) {
                $pulang[] = [
                    'nama' => $absen->user->nama_karyawan,
                    'status' => $absen->status_absensi_pulang,
                    'waktu' => $absen->waktu_absen_pulang,
                ];
            }
        }

        return [
            'pulang' => collect($pulang)->sortByDesc('waktu')->values(),
        ];
    }
};
?>



@php($attendance = $this->getRecentAttendance())
<div class="col-lg-6 d-flex align-items-stretch">
    <div class="card w-100">
        <div class="card-body p-4" style="min-height: 240px; max-height: 360px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #dee2e6 transparent;">
            <div class="mb-4">
                <h5 class="card-title fw-semibold">Pulang Lebih Cepat Hari Ini</h5>
            </div>

            @if($attendance['pulang']->isNotEmpty())
            <ul class="timeline-widget mb-0 position-relative mb-n5">
                @foreach($attendance['pulang'] as $item)
                <li class="timeline-item d-flex position-relative overflow-hidden">
                    <div class="timeline-time text-dark flex-shrink-0 text-end">
                        {{ \Carbon\Carbon::parse($item['waktu'])->format('H:i') }}
                    </div>
                    <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                        <span class="timeline-badge border-2 border border-danger flex-shrink-0 my-8"></span>
                    </div>
                    <div class="timeline-desc fs-3 text-dark mt-n1">
                        <span class="fw-semibold">{{ ucwords(strtolower($item['nama'])) }}</span>
                        <br>
                        <span class="text-danger">{{ $item['status'] }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <div class="text-center text-muted mb-3">
                Tidak ada yang pulang lebih cepat hari ini
            </div>
            @endif
        </div>
    </div>
</div>