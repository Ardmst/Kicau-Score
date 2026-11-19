<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JuriController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/scoreboard/{kategori:slug?}', [PublicController::class, 'scoreboard'])->name('scoreboard');
Route::get('/api/scoreboard/{kategori:slug?}', [PublicController::class, 'apiScoreboard'])->name('api.scoreboard');
Route::get('/burung/{burung:slug}', [PublicController::class, 'burung'])->name('burung.profile');
Route::get('/api/burung/{burung:slug}', [PublicController::class, 'burung'])->name('api.burung.profile');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Peserta Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:peserta,admin'])->prefix('peserta')->name('peserta.')->group(function () {
    Route::get('/dashboard', [PesertaController::class, 'dashboard'])->name('dashboard');
    Route::get('/burung', [PesertaController::class, 'burungIndex'])->name('burung.index');
    Route::post('/burung', [PesertaController::class, 'burungStore'])->name('burung.store');
    Route::put('/burung/{burung}', [PesertaController::class, 'burungUpdate'])->name('burung.update');
    Route::delete('/burung/{burung}', [PesertaController::class, 'burungDestroy'])->name('burung.destroy');
    Route::get('/daftar', [PesertaController::class, 'showDaftar'])->name('daftar');
    Route::post('/daftar', [PesertaController::class, 'storeDaftar'])->name('daftar.store');
    Route::get('/tiket/{pendaftaran}', [PesertaController::class, 'tiket'])->name('tiket');
});

/*
|--------------------------------------------------------------------------
| Juri Routes (Mobile-First Scoring)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:juri,admin'])->prefix('juri')->name('juri.')->group(function () {
    Route::get('/', [JuriController::class, 'dashboard'])->name('dashboard');
    Route::get('/penilaian/{pendaftaran}', [JuriController::class, 'scoringForm'])->name('penilaian.form');
    Route::post('/penilaian/{pendaftaran}', [JuriController::class, 'storeOrUpdateScore'])->name('penilaian.store');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/events', [AdminController::class, 'eventIndex'])->name('events.index');
    Route::post('/events', [AdminController::class, 'eventStore'])->name('events.store');
    Route::put('/events/{event}', [AdminController::class, 'eventUpdate'])->name('events.update');
    Route::post('/events/{event}/kategori', [AdminController::class, 'kategoriStore'])->name('kategori.store');
    Route::post('/kategori/{kategori}/status', [AdminController::class, 'updateKategoriStatus'])->name('kategori.status');
    Route::get('/verifikasi', [AdminController::class, 'verifikasiQueue'])->name('verifikasi.index');
    Route::post('/verifikasi/{pendaftaran}/acc', [AdminController::class, 'verifikasiAcc'])->name('verifikasi.acc');
    Route::post('/verifikasi/{pendaftaran}/reject', [AdminController::class, 'verifikasiReject'])->name('verifikasi.reject');
    Route::get('/users', [AdminController::class, 'userIndex'])->name('users.index');
    Route::post('/users', [AdminController::class, 'userStore'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'userDestroy'])->name('users.destroy');
    Route::post('/juri', [AdminController::class, 'createJuri'])->name('juri.store');
});
