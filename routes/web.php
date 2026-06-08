<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'auth.login')->name('login.page')->middleware('guest');
Route::get('/access-denied', function () {
    return view('403');
})->name('access-denied');

Route::group(['middleware' => ['auth', 'role:Admin'], 'prefix' => 'admin'], function () {
    Route::livewire('/dashboard', 'admin.dashboard.index')->name('dashboard.page');
    Route::livewire('/account', 'admin.account.index')->name('account.page');
    Route::livewire('/absensi', 'admin.absensi.index')->name('absensi.page');
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
});
