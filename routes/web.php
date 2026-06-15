<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/dashboard');
});

Route::prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/input-siswa', [AdminController::class, 'inputSiswa'])
        ->name('input.siswa');

    Route::post('/input-siswa/proses', [AdminController::class, 'prosesPrediksi'])
        ->name('input.siswa.proses');

    Route::get('/hasil-prediksi', [AdminController::class, 'hasilPrediksi'])
        ->name('hasil.prediksi');

    Route::get('/hasil-prediksi/{id}', [AdminController::class, 'hasilDetail'])
        ->name('hasil.prediksi.detail');

    Route::post('/upload-alumni/proses', [AdminController::class, 'prosesUploadAlumni'])
        ->name('upload.alumni.proses');

    Route::get('/info-model', [AdminController::class, 'infoModel'])
        ->name('info.model');

});






    
Route::get('/upload-alumni', [AdminController::class, 'uploadAlumni'])
    ->name('upload.alumni');
Route::post('/upload-alumni/proses', [AdminController::class, 'prosesUploadAlumni'])
    ->name('upload.alumni.proses');
Route::get('/info-model', [AdminController::class, 'infoModel'])
    ->name('info.model');
Route::get('/preprocessing', [AdminController::class, 'preprocessing'])
    ->name('preprocessing');

