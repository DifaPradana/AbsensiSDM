<?php

use App\Models\IzinAbsen;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $perPage = 10;
    public $search = '';
    public $statusFilter = 'menunggu konfirmasi'; // default


    #[On('success')]
    public function render()
    {
        $query = IzinAbsen::with('user')
            ->whereHas('user') // hanya user yang tidak soft delete
            ->latest();

        if ($this->search) {
            $query->whereHas(
                'user',
                fn($q) =>
                $q->where('nama_karyawan', 'like', '%' . $this->search . '%')
            );
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return $this->view([
            'izins' => $query->paginate($this->perPage),
        ])
            ->layout('layouts.main')
            ->title('izin | Pengajuan Izin');
    }

    public function editIzin($izin_id)
    {
        $this->dispatch('open-edit-izin', izin_id: $izin_id);
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <br>
        <div>
            <div class="mx-auto w-full px-4">
                <h5 class="card-title fw-semibold mb-4">Pengajuan Izin Absen</h5>
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <livewire:admin.izin.edit-izin />
                    {{-- Search Bar + Filter --}}
                    <div class="flex items-center justify-between p-4 gap-3 flex-wrap">
                        <div class="flex gap-2 flex-wrap flex-1">

                            {{-- Search --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="ti ti-search text-gray-400" style="font-size:15px"></i>
                                </div>
                                <input
                                    wire:model.live.debounce.300ms="search"
                                    type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block pl-9 p-2 min-w-[200px]"
                                    placeholder="Cari nama karyawan...">
                            </div>

                            {{-- Filter Status --}}
                            <div class="relative">
                                <select
                                    wire:model.live="statusFilter"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2 pr-8 appearance-none cursor-pointer">
                                    <option value="all">Semua Status</option>
                                    <option value="menunggu konfirmasi">Menunggu Konfirmasi</option>
                                    <option value="disetujui">Disetujui</option>
                                    <option value="ditolak">Ditolak</option>
                                </select>
                            </div>

                        </div>

                        {{-- Badge jumlah hasil --}}
                        <div>
                            @php
                            $badgeMap = [
                            'menunggu konfirmasi' => 'bg-yellow-100 text-yellow-800',
                            'disetujui' => 'bg-green-100 text-green-800',
                            'ditolak' => 'bg-red-100 text-red-800',
                            'all' => 'bg-gray-100 text-gray-700',
                            ];
                            $badgeClass = $badgeMap[$statusFilter] ?? 'bg-gray-100 text-gray-700';
                            $labelMap = [
                            'menunggu konfirmasi' => 'Menunggu Konfirmasi',
                            'disetujui' => 'Disetujui',
                            'ditolak' => 'Ditolak',
                            'all' => 'Semua Status',
                            ];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                {{ $labelMap[$statusFilter] ?? '-' }}: {{ $izins->total() }} data
                            </span>
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300 text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-center">Nama</th>
                                    <th class="border px-4 py-3 text-center">Tipe Izin</th>
                                    <th class="border px-4 py-3 text-center">Durasi</th>
                                    <th class="border px-4 py-3 text-center">Tanggal Mulai</th>
                                    <th class="border px-4 py-3 text-center">Tanggal Akhir</th>
                                    <th class="border px-4 py-3 text-center">Dokumen Izin</th>
                                    <th class="border px-4 py-3 text-center">Note</th>
                                    <th class="border px-4 py-3 text-center">Status</th>
                                    <th class="border px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($izins as $index => $izin)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50">
                                    <td class="border px-4 py-3 text-center font-medium text-gray-900">
                                        {{ ucwords($izin->user->nama_karyawan ?? '-') }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @php
                                        $status = $izin->tipe_izin ?? '-';
                                        $badgeClass = match(strtolower($status)) {
                                        'hadir' => 'bg-green-100 text-green-800',
                                        'izin' => 'bg-yellow-100 text-yellow-800',
                                        'sakit' => 'bg-blue-100 text-blue-800',
                                        'alfa' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                        };

                                        $durasi = $izin->akhir_izin
                                        ? \Carbon\Carbon::parse($izin->mulai_izin)
                                        ->diffInDays($izin->akhir_izin) + 1
                                        : 1;
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ $durasi }} Hari
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ \Carbon\Carbon::parse($izin->mulai_izin)->translatedFormat('l, d F Y') }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ \Carbon\Carbon::parse($izin->akhir_izin)->translatedFormat('l, d F Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($izin->dokumen_izin)
                                        <button type="button"
                                            onclick="showModal('modalDokumen{{ $izin->izin_id }}')"
                                            class="btn btn-info btn-sm"
                                            style="width: 40px; margin: 0 auto;">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        @else
                                        <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        {{ $izin->note ?? '-' }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        @php
                                        $status = $izin->status ?? '-';
                                        $badgeClass = match(strtolower($status)) {
                                        'disetujui' => 'bg-green-100 text-green-800',
                                        'menunggu konfirmasi' => 'bg-yellow-100 text-yellow-800',
                                        'ditolak' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                        };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $badgeClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3  text-center text-black">
                                        <button
                                            type="button"
                                            wire:click="editIzin({{ $izin->izin_id }})"
                                            wire:loading.attr="disabled"
                                            class="btn btn-warning m-1">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                    </td>
                                </tr>

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

                                @empty
                                <tr>
                                    <td colspan="12" class="border px-4 py-6 text-center text-gray-400 italic">
                                        Tidak ada data izin.
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
                                Menampilkan {{ $izins->firstItem() ?? 0 }}–{{ $izins->lastItem() ?? 0 }}
                                dari {{ $izins->total() }} data
                            </div>
                        </div>

                        <div class="mt-3">
                            {{ $izins->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <br>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-edit-modal', () => {
                const modalEl = document.getElementById('editIzinModal');

                let modal = bootstrap.Modal.getInstance(modalEl);

                if (!modal) {
                    modal = new bootstrap.Modal(modalEl);
                }

                if (!modalEl.classList.contains('show')) {
                    modal.show();
                }
            });
        });

        function showModal(id) {
            const modalEl = document.getElementById(id);

            let modal = bootstrap.Modal.getInstance(modalEl);

            if (!modal) {
                modal = new bootstrap.Modal(modalEl);
            }

            modal.show();
        }

        function hideModal(id) {
            const modalEl = document.getElementById(id);

            let modal = bootstrap.Modal.getInstance(modalEl);

            if (modal) {
                modal.hide();
            }
        }
    </script>
</div>