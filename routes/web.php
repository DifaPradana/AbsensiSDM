<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'auth.login')->name('login');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
Route::get('/access-denied', function () {
    return view('403');
})->name('access-denied');

Route::group(['middleware' => ['auth', 'role:admin'], 'prefix' => 'admin'], function () {
    Route::livewire('/dashboard', 'admin.dashboard.index')->name('dashboard.page');
    Route::livewire('/account', 'admin.account.index')->name('account.page');
    Route::livewire('/role', 'admin.role.index')->name('role.page');
    Route::livewire('/lokasi', 'admin.lokasi.index')->name('lokasi.page');
    Route::livewire('/absensi', 'admin.absensi.index')->name('absensi.page');
    Route::livewire('/pengajuan-izin', 'admin.izin.index')->name('izin.page');
    Route::livewire('/exported-absensi', 'admin.export-absensi.index')->name('exported-absensi.page');
});

Route::group(['middleware' => ['auth', 'role:pegawai kantor, pegawai lapangan'], 'prefix' => 'karyawan'], function () {
    Route::livewire('/post-absensi', 'karyawan.absensi')->name('karyawan.absensi.page');
    Route::livewire('/profile', 'karyawan.profile')->name('karyawan.profile.page');
    Route::livewire('/riwayat-kehadiran', 'karyawan.kehadiran')->name('karyawan.kehadiran.page');
    Route::livewire('/izin-absen', 'karyawan.izin-absen')->name('karyawan.izin-absen.page');
    Route::livewire('/history-izin-absen', 'karyawan.history-izin')->name('karyawan.history-izin');
});
