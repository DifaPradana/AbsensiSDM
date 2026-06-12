<?php

use App\Models\ExportAbsen;
use Livewire\Component;

new class extends Component
{
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
    {{-- It always seems impossible until it is done. - Nelson Mandela --}}
</div>