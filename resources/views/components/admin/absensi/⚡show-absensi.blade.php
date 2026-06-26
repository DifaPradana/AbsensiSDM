<?php

use App\Models\Absensi;
use App\Jobs\ExportAbsensiJob;
use App\Models\ExportFile;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    public $search = '';
    public $tanggalAwal;
    public $tanggalAkhir;
    public $perPage = 20;
    public bool $isExporting = false;
    public bool $exportDone = false;
    protected $paginationTheme = 'bootstrap';

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function exportCsv(): void
    {
        $this->isExporting = true;
        $this->exportDone = false;

        cache()->forget('export_done'); // ✅

        ExportAbsensiJob::dispatch(
            $this->search,
            $this->tanggalAwal,
            $this->tanggalAkhir,
        );
    }

    public function checkExport(): void
    {
        if (! $this->isExporting) return;

        if (cache()->get('export_done')) { // ✅
            $this->isExporting = false;
            $this->exportDone = true;
            cache()->forget('export_done'); // ✅

            LivewireAlert::title('Berhasil Export Absensi')
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
        return $this->view([
            'absensis' => Absensi::with('user')
                ->whereHas('user')
                ->search($this->search)
                ->filterTanggal($this->tanggalAwal, $this->tanggalAkhir)
                ->latest()
                ->paginate($this->perPage),
        ])
            ->layout('layouts.main')
            ->title('Absensi | Account');
    }


    public function batalkanLembur($absensi_id)
    {
        $absensi = Absensi::findOrFail($absensi_id);

        $absensi->update([
            'status_absensi_pulang' => 'On Time'
        ]);

        LivewireAlert::title('Status lembur dibatalkan')
            ->success()
            ->timer(2000)
            ->toast()
            ->position('top-end')
            ->show();
    }
};
?>

<div class="container-fluid">

    {{-- Polling hanya aktif saat export berjalan --}}
    @if($isExporting)
    <div wire:poll.3s="checkExport"></div>
    @endif

    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Filter Tanggal</h5>

            <div class="row align-items-end g-3">
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

                {{-- Tombol Reset + Export --}}
                <div class="col-md-4 d-flex gap-2">
                    <button type="button" wire:click="resetFilter" class="btn btn-outline-secondary w-100">
                        Reset
                    </button>

                    @if(!$isExporting)
                    <button type="button" wire:click="exportCsv" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-1">
                        <i class="ti ti-file-spreadsheet"></i> Export CSV
                    </button>
                    @else
                    <button type="button" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-1" disabled>
                        <span class="spinner-border spinner-border-sm" role="status"></span>
                        Memproses...
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <div>
            <div class="mx-auto w-full px-4">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">

                    {{-- Search Bar --}}
                    <div class="flex items-center justify-between p-4">
                        <div class="flex">
                            <div class="relative w-full">
                                <input
                                    wire:model.live.debounce.300ms="search"
                                    type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2"
                                    placeholder="Search nama...">
                            </div>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300 text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-center">Tanggal</th>
                                    <th class="border px-4 py-3 text-center">Nama</th>
                                    <th class="border px-4 py-3 text-center">Role</th>
                                    <th class="border px-4 py-3 text-center">Status</th>
                                    <th class="border px-4 py-3 text-center">Waktu Masuk</th>
                                    <th class="border px-4 py-3 text-center">Lokasi Masuk</th>
                                    <th class="border px-4 py-3 text-center">Note Masuk</th>
                                    <th class="border px-4 py-3 text-center">Waktu Pulang</th>
                                    <th class="border px-4 py-3 text-center">Lokasi Pulang</th>
                                    <th class="border px-4 py-3 text-center">Note Pulang</th>
                                    <th class="border px-4 py-3 text-center">Foto Masuk</th>
                                    <th class="border px-4 py-3 text-center">Foto Pulang</th>
                                    <th class="border px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($absensis as $index => $absensi)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50">
                                    <td class="border px-4 py-3 text-center text-gray-500">
                                        {{ $absensi->waktu_absen_masuk ? $absensi->waktu_absen_masuk->translatedFormat('l, d F') : '-' }}
                                    </td>
                                    <td class="border px-4 py-3 text-center font-medium text-gray-900">
                                        {{ ucwords($absensi->user->nama_karyawan ?? '-') }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ ucwords($absensi->user->role->nama_role ?? '-') }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @php
                                        $status = $absensi->tipe_absensi ?? '-';
                                        $badgeClass = match(strtolower($status)) {
                                        'hadir' => 'bg-green-100 text-green-800',
                                        'izin' => 'bg-yellow-100 text-yellow-800',
                                        'sakit' => 'bg-blue-100 text-blue-800',
                                        'alpha' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                        };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @php
                                        $badgeClass = match(true) {
                                        $absensi['status_absensi_masuk'] === '-' => 'bg-danger',
                                        $absensi['status_absensi_masuk'] === 'On Time' => 'bg-success',
                                        str_starts_with($absensi['status_absensi_masuk'], 'Terlambat') => 'bg-warning',
                                        default => 'bg-dark',
                                        };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-3 fw-semibold">
                                            {{ $absensi['status_absensi_masuk'] }}
                                        </span>
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @if($absensi->lokasi_masuk === 'Unknown')
                                        @if($absensi->latitude_masuk && $absensi->longitude_masuk)
                                        <a href="https://www.google.com/maps?q={{ $absensi->latitude_masuk }},{{ $absensi->longitude_masuk }}"
                                            target="_blank" class="text-blue-600 hover:underline">
                                            {{ $absensi->latitude_masuk }}, {{ $absensi->longitude_masuk }}
                                        </a>
                                        @else
                                        -
                                        @endif
                                        @else
                                        {{ $absensi->lokasi_masuk ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ $absensi->note_masuk ?? '-' }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @php
                                        $status = $absensi['status_absensi_pulang'] ?? '';
                                        $badgeClass = match(true) {
                                        str_starts_with($status, '-') => 'bg-danger',
                                        str_starts_with($status, 'Pulang Lebih Cepat') => 'bg-warning',
                                        empty($status) => '',
                                        default => 'bg-success',
                                        };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} rounded-3 fw-semibold">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @if($absensi->lokasi_pulang === 'Unknown')
                                        @if($absensi->latitude_pulang && $absensi->longitude_pulang)
                                        <a href="https://www.google.com/maps?q={{ $absensi->latitude_pulang }},{{ $absensi->longitude_pulang }}"
                                            target="_blank" class="text-blue-600 hover:underline">
                                            {{ $absensi->latitude_pulang }}, {{ $absensi->longitude_pulang }}
                                        </a>
                                        @else
                                        -
                                        @endif
                                        @else
                                        {{ $absensi->lokasi_pulang ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ $absensi->note_pulang ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($absensi->photo_masuk)
                                        <button type="button"
                                            onclick="showModal('modalMasuk{{ $absensi->absensi_id }}')"
                                            class="btn btn-info btn-sm"
                                            style="width: 40px; margin: 0 auto;">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        @else
                                        <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($absensi->photo_pulang)
                                        <button type="button"
                                            onclick="showModal('modalPulang{{ $absensi->absensi_id }}')"
                                            class="btn btn-info btn-sm"
                                            style="width: 40px; margin: 0 auto;">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        @else
                                        <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($absensi->status_absensi_pulang && str_contains($absensi->status_absensi_pulang, 'Lembur'))
                                        <button
                                            type="button"
                                            wire:click="batalkanLembur({{ $absensi->absensi_id }})"
                                            wire:confirm="Apakah kamu yakin ingin membatalkan lembur {{ ucwords($absensi->user->nama_karyawan) }}? Status pulang akan diubah menjadi On Time."
                                            wire:loading.attr="disabled"
                                            wire:target="batalkanLembur"
                                            class="btn btn-warning btn-sm d-inline-flex align-items-center gap-1">
                                            <wire:loading.remove wire:target="batalkanLembur">
                                                <i class="ti ti-clock-off"></i>
                                            </wire:loading.remove>
                                            Batalkan Lembur
                                        </button>
                                        @else
                                        <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Modal Foto Masuk --}}
                                <div class="modal fade" id="modalMasuk{{ $absensi->absensi_id }}" tabindex="-1" aria-hidden="true" wire:ignore.self>
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Foto Masuk — <strong>{{ $absensi->user->nama_karyawan ?? '-' }}</strong></h5>
                                                <button type="button" class="btn-close"
                                                    onclick="hideModal('modalMasuk{{ $absensi->absensi_id }}')"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                @php
                                                $ext = strtolower(pathinfo($absensi->photo_masuk, PATHINFO_EXTENSION));
                                                @endphp

                                                @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                                <img
                                                    src="{{ asset('storage/'.$absensi->photo_masuk) }}"
                                                    class="img-fluid rounded">
                                                @elseif($ext === 'pdf')
                                                <iframe
                                                    src="{{ asset('storage/'.$absensi->photo_masuk) }}"
                                                    width="100%"
                                                    height="600">
                                                </iframe>
                                                @else
                                                <span class="text-muted">Dokumen tidak tersedia</span>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary"
                                                    onclick="hideModal('modalMasuk{{ $absensi->absensi_id }}')">Tutup</button>
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
                                                <button type="button" class="btn-close"
                                                    onclick="hideModal('modalPulang{{ $absensi->absensi_id }}')"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                @php
                                                $ext = strtolower(pathinfo($absensi->photo_pulang, PATHINFO_EXTENSION));
                                                @endphp

                                                @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                                <img
                                                    src="{{ asset('storage/'.$absensi->photo_pulang) }}"
                                                    class="img-fluid rounded">
                                                @elseif($ext === 'pdf')
                                                <iframe
                                                    src="{{ asset('storage/'.$absensi->photo_pulang) }}"
                                                    width="100%"
                                                    height="600">
                                                </iframe>
                                                @else
                                                <span class="text-muted">Dokumen tidak tersedia</span>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-primary"
                                                    onclick="hideModal('modalPulang{{ $absensi->absensi_id }}')">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @empty
                                <tr>
                                    <td colspan="12" class="border px-4 py-6 text-center text-gray-400 italic">
                                        Tidak ada data absensi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Per Page & Pagination --}}
                    <div class="py-4 px-3">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex space-x-4 items-center">
                                <label class="text-sm font-medium text-gray-900 whitespace-nowrap">Per Page</label>
                                <select
                                    wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>

                            <div class="text-sm text-gray-500">
                                Menampilkan {{ $absensis->firstItem() ?? 0 }}–{{ $absensis->lastItem() ?? 0 }}
                                dari {{ $absensis->total() }} data
                            </div>
                        </div>

                        <div class="mt-3">
                            {{ $absensis->links('livewire::bootstrap') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <br>
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