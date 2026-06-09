<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Profile Karyawan');
    }
};
?>

<div>
    <livewire:karyawan.profile.edit-profile />
</div>