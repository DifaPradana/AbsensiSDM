<?php

use App\Models\ExportAbsen;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithPagination;

    public $perPage = 10;

    public function deleteExport(int $id): void
    {
        $export = ExportAbsen::findOrFail($id);
        Storage::disk('public')->delete($export->path);
        $export->delete();
    }

    public function render()
    {
        return $this->view([
            'exports' => ExportAbsen::latest()->paginate($this->perPage),
        ])
            ->layout('layouts.main')
            ->title('Absensi | Riwayat Export');
    }
};
?>

<div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="card-title fw-semibold mb-1">Riwayat Export Absensi</h5>
                        <p class="text-muted small mb-0">File tersimpan selama 7 hari, lalu otomatis dihapus.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary fw-semibold px-3 py-2">
                        Total: {{ ExportAbsen::count() }} file
                    </span>
                </div>
            </div>

            <div class="mx-auto w-full px-4">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">

                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300 text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th class="border px-4 py-3 text-center">No</th>
                                    <th class="border px-4 py-3 text-center">Nama File</th>
                                    <th class="border px-4 py-3 text-center">Dibuat</th>
                                    <th class="border px-4 py-3 text-center">Kedaluwarsa</th>
                                    <th class="border px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exports as $i => $export)
                                @php
                                $expiredAt = $export->created_at->addWeek();
                                $isExpiringSoon = $expiredAt->diffInDays(now()) <= 1;
                                    @endphp
                                    <tr wire:key="{{ $export->id }}" class="bg-white border-b hover:bg-gray-50">
                                    <td class="border px-4 py-3 text-center text-gray-500">
                                        {{ $exports->firstItem() + $i }}
                                    </td>
                                    <td class="border px-4 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="ti ti-file-spreadsheet text-success fs-5"></i>
                                            <span class="fw-medium text-gray-800">{{ $export->filename }}</span>
                                        </div>
                                    </td>
                                    <td class="border px-4 py-3 text-center text-gray-500">
                                        {{ $export->created_at->translatedFormat('d F Y, H:i') }}
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        <span class="badge {{ $isExpiringSoon ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }} fw-semibold px-2 py-1">
                                            {{ $expiredAt->translatedFormat('d F Y, H:i') }}
                                        </span>
                                    </td>
                                    <td class="border px-4 py-3 text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ $export->url }}" download
                                                class="btn btn-success btn-sm d-flex align-items-center gap-1">
                                                <i class="ti ti-download"></i> Download
                                            </a>
                                            <button
                                                wire:click="deleteExport({{ $export->id }})"
                                                wire:confirm="Yakin ingin menghapus file ini?"
                                                class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="border px-4 py-8 text-center">
                                            <div class="d-flex flex-column align-items-center gap-2 text-gray-400">
                                                <i class="ti ti-file-off fs-1"></i>
                                                <span class="italic">Belum ada file export.</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="py-4 px-3">
                        <div class="flex items-center justify-between flex-wrap gap-3">
                            <div class="flex space-x-4 items-center">
                                <label class="text-sm font-medium text-gray-900 whitespace-nowrap">Per Page</label>
                                <select wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <div class="text-sm text-gray-500">
                                Menampilkan {{ $exports->firstItem() ?? 0 }}–{{ $exports->lastItem() ?? 0 }}
                                dari {{ $exports->total() }} file
                            </div>
                        </div>
                        <div class="mt-3">
                            {{ $exports->links() }}
                        </div>
                    </div>

                </div>
            </div>
            <br>
        </div>
    </div>
</div>