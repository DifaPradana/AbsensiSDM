<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Data Absensi');
    }
};
?>

<div>
    <div class="row">
        <livewire:admin.absensi.show-absensi />
    </div>
</div>