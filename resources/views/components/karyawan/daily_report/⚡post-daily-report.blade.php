<?php

use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public $dokumen_daily_report;

    public function submitDailyReport()
    {
        $user = Auth::user();

        $dailyReportAktif = DailyReport::where('user_id', $user->user_id)
            ->whereDate('created_at', today())
            ->exists();

        if ($dailyReportAktif) {
            $this->dispatch('daily-report-error', message: 'Kamu sudah mengupload Daily Report hari ini');
            return;
        }

        $validate = Validator::make(
            ['dokumen_daily_report' => $this->dokumen_daily_report],
            ['dokumen_daily_report' => 'required|file|mimes:pdf|max:2560'],
            [
                'dokumen_daily_report.required' => 'Dokumen Daily Report harus diisi',
                'dokumen_daily_report.mimes'    => 'Dokumen Daily Report harus berbentuk .pdf',
                'dokumen_daily_report.max'      => 'Dokumen Daily Report tidak boleh lebih dari 2 MB',
            ]
        );

        if ($validate->fails()) {
            $this->dispatch('daily-report-error', message: $validate->errors()->first());
            return;
        }

        $filename     = Str::uuid() . '.pdf';
        $relativePath = $this->dokumen_daily_report
            ->storePubliclyAs('dokumen-daily-report', $filename, 'public');

        DailyReport::create([
            'user_id'       => $user->user_id,
            'path_dokumen'  => $relativePath,
        ]);

        $this->reset();

        $this->dispatch('daily-report-success');

        LivewireAlert::title('Berhasil')
            ->text('Berhasil Upload Daily Report')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }
};
?>


<div>
    <form wire:submit.prevent="submitDailyReport">
        <div class="card">
            <div class="card-body">
                <p class="section-label">Dokumen Daily Report</p>
                <input type="file" wire:model="dokumen_daily_report" accept=".pdf" class="form-control" id="dokumen_daily_report">
                <div wire:loading wire:target="dokumen_daily_report" class="text-primary mt-2">
                    <span class="spinner-border spinner-border-sm"></span>
                    Uploading...
                </div>
                <div class="divider"></div>

                <div class="d-flex justify-content-center">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="dokumen_daily_report">
                        <i class="ti ti-send" style="font-size:15px;"></i>
                        <span>Kirim Daily Report</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>