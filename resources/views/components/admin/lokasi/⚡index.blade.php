<?php

use App\Models\LokasiAbsensi;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';

    public $nama_lokasi;
    public $latitude_lokasi;
    public $longitude_lokasi;
    public $radius_meter;

    public function render()
    {
        return $this->view([
            'lokasi' => LokasiAbsensi::query()
                ->search($this->search)
                ->orderBy('lokasi_id', 'asc')
                ->paginate($this->perPage)
        ])
            ->layout('layouts.main')
            ->title('Absensi | Lokasi');
    }


    #[On('success')]
    public function refreshData()
    {
        $this->resetPage();
    }

    public function delete(LokasiAbsensi $lokasi)
    {
        $lokasi->delete();
        LivewireAlert::title('Berhasil')
            ->text('Kamu berhasil delete lokasi')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function editLokasi($lokasi_id)
    {
        $this->dispatch('open-edit-lokasi', lokasi_id: $lokasi_id);
        // dd("Kirim dispatch + $user_id");
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Manajemen Lokasi</h5>
            <a href="#tambahLokasiModal" data-bs-toggle="modal" class="btn btn-primary m-1">
                Tambah Lokasi
            </a>
        </div>
        <livewire:admin.lokasi.create-lokasi />
        <livewire:admin.lokasi.edit-lokasi />

        <div>
            {{-- <section class="mt-10"> --}}
            <div class="mx-auto w-full px-4">
                <!-- Start coding here -->
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between d p-4">
                        <div class="flex">
                            <div class="relative w-full">
                                <input wire:model.live.debounce.300ms="search" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 "
                                    placeholder="Search" required="">
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300 text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="border border px-4 py-3 text-center">Nama lokasi</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Latitude</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Longitude</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Radius Meter</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if ($lokasi->isEmpty())
                                <tr>
                                    <td colspan="6" class="border px-4 py-3 text-center text-black">Tidak ditemukan</td>
                                </tr>
                                @else
                                @foreach ($lokasi as $item)
                                <tr wire:key="{{ $item->lokasi_id }}" class="border-b dark:border-gray-300">
                                    <td class="border px-4 py-3 text-black text-center">{{ $item->nama_lokasi }}
                                    </td>
                                    <td class="border px-4 py-3 text-black text-center">{{ $item->latitude_lokasi }}
                                    </td>
                                    <td class="border px-4 py-3 text-black text-center">{{ $item->longitude_lokasi }}
                                    </td>
                                    <td class="border px-4 py-3 text-black text-center">{{ $item->radius_meter }}
                                    </td>
                                    <td class="px-4 py-3  text-center text-black">
                                        <button
                                            type="button"
                                            wire:click="editLokasi({{ $item->lokasi_id }})"
                                            wire:loading.attr="disabled"
                                            class="btn btn-warning m-1">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <button
                                            onclick="confirm('Kamu akan menghapus Lokasi {{ $item->nama_lokasi }} secara permanen, apakah yakin?') || event.stopImmediatePropagation()"
                                            wire:click="delete({{ $item->lokasi_id }})"
                                            class="btn btn-danger m-1">
                                            <i class="ti ti-trash" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="py-4 px-3">
                        <div class="flex ">
                            <div class="flex space-x-4 items-center mb-3">
                                <label class="w-32 text-sm font-medium text-gray-900">Per Page</label>
                                <select wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        {{ $lokasi->links() }}
                    </div>
                </div>
                {{-- </section> --}}
            </div>
        </div>
        <br>
    </div>
    <script>
        function registerListeners() {
            Livewire.on('show-edit-modal', () => {
                const modalEl = document.getElementById('editLokasiModal');
                if (!modalEl) return;
                let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                if (!modalEl.classList.contains('show')) modal.show();
            });

            Livewire.on('hide-edit-modal', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editLokasiModal'));
                modal?.hide();
            });
        }

        document.addEventListener('livewire:init', registerListeners);
        document.addEventListener('livewire:navigated', registerListeners);
    </script>
</div>