<?php

use App\Http\Controllers\AutentikasiController;
use App\Http\Controllers\LaporanBulananController;
use App\Http\Controllers\PengeluaranController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [AutentikasiController::class, 'login_auth']);
Route::post('/register', [AutentikasiController::class, 'daftar_sintakqu']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/simpan-transaksi', [PengeluaranController::class, 'create']);
    Route::get('/cari-transaksi/{unikTgl}', [PengeluaranController::class, 'show']);
    Route::get('/cari-by-id/{id}', [PengeluaranController::class, 'cari_data_by_id']);
    Route::patch('/edit-transaksi/{id}', [PengeluaranController::class, 'edit']);
    Route::delete('/hapus-transaksi/{id}', [PengeluaranController::class, 'destroy']);

    Route::get('/get-data-transaksi', [LaporanBulananController::class, 'index']);
    Route::get('/get-data-transaksi', [LaporanBulananController::class, 'index']);
    Route::get('/get-data-transaksi', [LaporanBulananController::class, 'index']);
    Route::get('/get-data-transaksi', [LaporanBulananController::class, 'index']);

    Route::post('/logout', [AutentikasiController::class, 'logout_action']);
});
