<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'auth.login')->name('login');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
Route::get('/access-denied', function () {
    return view('403');
})->name('access-denied');

Route::group(['middleware' => ['auth', 'role:Admin'], 'prefix' => 'admin'], function () {
    Route::livewire('/dashboard', 'admin.dashboard.index')->name('dashboard.page');
    Route::livewire('/account', 'admin.account.index')->name('account.page');
    Route::livewire('/absensi', 'admin.absensi.index')->name('absensi.page');
});

Route::group(['middleware' => ['auth', 'role:Pegawai Kantor, Pegawai Lapangan'], 'prefix' => 'karyawan'], function () {
    Route::livewire('/post-absensi', 'karyawan.absensi')->name('karyawan.absensi.page');
    Route::livewire('/profile', 'karyawan.profile')->name('karyawan.profile.page');
});
