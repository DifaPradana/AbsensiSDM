<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Riwayat Kehadiran');
    }
};
?>

<div>
    <livewire:karyawan.kehadiran.show-kehadiran />
</div>