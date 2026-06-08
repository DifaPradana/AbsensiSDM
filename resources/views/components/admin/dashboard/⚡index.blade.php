<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Absensi | Dashboard');
    }
};
?>


<div>
    @push('title')
    <title>Dashboard </title>
    @endpush


    <div class="row">
        <livewire:admin.dashboard.recent-late-attendance-component />
        <livewire:admin.dashboard.recent-early-checkout-attendance-component />
        <livewire:admin.dashboard.recent-attendance-component />
    </div>
</div>