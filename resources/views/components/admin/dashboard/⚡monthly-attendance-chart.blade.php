<?php

use Livewire\Component;

new class extends Component
{
    public function getAllMonthlyAttendance() {}
};
?>

<div class="col-lg-12">
    <!-- Monthly Attendance -->
    <div class="card overflow-hidden">
        <div class="card-body p-4">
            <h5 class="card-title mb-9 fw-semibold">Monthly Attendance</h5>
            <div class="row align-items-center">
                <div class="col-8">
                    <!-- <h4 class="fw-semibold mb-3">$36,358</h4> -->
                    <div class="d-flex align-items-center mb-3">
                        <!-- <span
                            class="me-1 rounded-circle bg-light-success round-20 d-flex align-items-center justify-content-center">
                            <i class="ti ti-arrow-up-left text-success"></i>
                        </span> -->
                        <!-- <p class="text-dark me-1 fs-3 mb-0">+9%</p>
                        <p class="fs-3 mb-0">last year</p> -->
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                            <span class="fs-2">2023</span>
                        </div>
                        <div>
                            <span class="round-8 bg-light-primary rounded-circle me-2 d-inline-block"></span>
                            <span class="fs-2">2023</span>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="d-flex justify-content-center">
                        <div id="breakup"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>