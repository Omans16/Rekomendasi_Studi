<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UploadSiswaController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landingpage');
})->name('landingpage');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.proses');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.proses');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/redirect-dashboard', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    if (Auth::user()->role === 'siswa') {
        return redirect()->route('siswa.dashboard');
    }

    return redirect()->route('admin.dashboard');
})->name('redirect.dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN / GURU BK ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin,guru_bk'])
    ->group(function () {
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

        Route::get('/upload-siswa', [UploadSiswaController::class, 'index'])
            ->name('upload.siswa');

        Route::post('/upload-siswa/proses', [UploadSiswaController::class, 'store'])
            ->name('upload.siswa.proses');

        Route::post('/upload-alumni/proses', [AdminController::class, 'prosesUploadAlumni'])
            ->name('upload.alumni.proses');

        Route::get('/info-model', [AdminController::class, 'infoModel'])
            ->name('info.model');
    });

/*
|--------------------------------------------------------------------------
| SISWA ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('siswa')
    ->name('siswa.')
    ->middleware(['auth', 'role:siswa'])
    ->group(function () {
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
    });