<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/all-user', [UserController::class, 'getAllUser']);
Route::get('/get-user/{id}', [UserController::class, 'getUserById']);
Route::post('/create-user', [UserController::class, 'createUser']);
Route::post('/edit-user/{id}', [UserController::class, 'editUser']);
Route::delete('/delete-user/{id}', [UserController::class, 'deleteUserById']);


Route::post('/login', [AuthController::class, 'login']);


Route::group(['middleware' =>  ['auth:sanctum']], function () {
    Route::get('/me', [AuthController::class, 'loginChecker']);
    Route::get('/check-login', [AuthController::class, 'checkUserLogin']);
    Route::post('/edit-user', [AuthController::class, 'editUser']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/get-absen', [AbsensiController::class, 'getAllAbsen']);
    Route::get('/get-all-my-absen', [AbsensiController::class, 'getAllMyAbsen']);
    Route::post('/absen', [AbsensiController::class, 'absen']);
    Route::get('/getTodayAbsen', [AbsensiController::class, 'getTodayAbsen']);
});

Route::group(['middleware' => ['auth:sanctum', 'role:1']], function () {});
