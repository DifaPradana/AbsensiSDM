<?php

use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('Daily Report | Data Daily Report');
    }
};
?>

<div>
    <div class="row">
        <livewire:admin.daily-report.show-daily-report />
    </div>
</div>