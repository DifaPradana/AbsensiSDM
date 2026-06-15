<?php

use App\Models\Absensi;
use Livewire\Component;

new class extends Component
{

    public $attendances = [];

    public function mount()
    {
        $absensis = Absensi::with('user.role')
            ->whereHas('user', function ($query) {
                $query->withTrashed(); // include soft-deleted users
            })
            ->whereDate('waktu_absen_masuk', today())
            ->latest()
            ->get();

        $attendances = [];

        foreach ($absensis as $absen) {

            if (!empty($absen->waktu_absen_masuk)) {
                $attendances[] = [
                    'nama' => $absen->user->nama_karyawan,
                    'role' => $absen->user->role->nama_role ?? '-',
                    'tipe' => 'Masuk',
                    'status' => $absen->status_absensi_masuk,
                    'waktu' => $absen->waktu_absen_masuk,
                ];
            }

            if (!empty($absen->waktu_absen_pulang)) {
                $attendances[] = [
                    'nama' => $absen->user->nama_karyawan,
                    'role' => $absen->user->role->nama_role ?? '-',
                    'tipe' => 'Pulang',
                    'status' => $absen->status_absensi_pulang,
                    'waktu' => $absen->waktu_absen_pulang,
                ];
            }
        }

        $this->attendances = collect($attendances)
            ->sortByDesc('waktu')
            ->values()
            ->toArray(); // opsional
    }
};
?>

<div class="col-lg-12 d-flex align-items-stretch">
    <div class="card w-100">
        <div class="card-body p-4" style="height: 480px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: #dee2e6 transparent;">
            <h5 class="card-title fw-semibold mb-4">Recent Attendance</h5>
            <div class="table-responsive">
                <table class="table text-nowrap mb-0 align-middle">
                    <thead class="text-dark fs-4">
                        <tr>
                            <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">No</h6>
                            </th>
                            <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">Name</h6>
                            </th>
                            <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">Tipe Absensi</h6>
                            </th>
                            <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">Status</h6>
                            </th>
                            <th class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">Time</h6>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->attendances ?? [] as $index => $item)
                        <tr>
                            <td class="border-bottom-0">
                                <h6 class="fw-semibold mb-0">{{ $index + 1 }}</h6>
                            </td>
                            <td class="border-bottom-0">
                                <h6 class="fw-semibold mb-1">{{ ucwords(strtolower($item['nama'])) }}</h6>
                                <span class="fw-normal">{{ $item['role'] }}</span>
                            </td>
                            <td class="border-bottom-0">
                                <span class="badge rounded-3 fw-semibold {{ $item['tipe'] === 'Masuk' ? 'bg-light-primary text-primary' : 'bg-light-primary text-primary' }}">
                                    {{ $item['tipe'] }}
                                </span>
                            </td>
                            <td class="border-bottom-0">
                                @php
                                $badgeClass = match(true) {
                                str_starts_with($item['status'], 'Terlambat') => 'bg-danger',
                                str_starts_with($item['status'], 'Pulang Lebih Cepat') => 'bg-warning',
                                default => 'bg-success',
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-3 fw-semibold">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="border-bottom-0">
                                <h6 class="fw-semibold mb-0 fs-4">
                                    {{ $item['waktu'] ? \Carbon\Carbon::parse($item['waktu'])->format('H:i') : '-' }}
                                </h6>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted border-bottom-0">
                                Tidak ada data absensi hari ini
                            </td>
                        </tr>

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>