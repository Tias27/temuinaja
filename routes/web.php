<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\PublicLaporanController;
use Illuminate\Support\Facades\Route;

Route::pattern('laporan', 'TA-[A-Z0-9-]{1,60}');
Route::pattern('kode', 'TA-[A-Z0-9-]{1,60}');

Route::get('/', [PublicLaporanController::class, 'home'])->middleware('throttle:catalog')->name('home');
Route::get('/halaman/{page}', [PublicLaporanController::class, 'home'])
    ->whereNumber('page')
    ->middleware('throttle:catalog')
    ->name('home.page');

if (app()->environment('local')) {
    Route::get('/mockup', [PublicLaporanController::class, 'mockup'])->name('mockup');
    Route::get('/mockup/halaman/{page}', [PublicLaporanController::class, 'mockup'])
        ->whereNumber('page')
        ->name('mockup.page');
}

Route::post('/barang/{jenis}/cari', [PublicLaporanController::class, 'search'])
    ->whereIn('jenis', ['ditemukan', 'hilang'])
    ->middleware('throttle:catalog-search')
    ->name('laporan.search');
Route::get('/barang/{jenis}/cari/{term}/halaman/{page?}', [PublicLaporanController::class, 'index'])
    ->whereIn('jenis', ['ditemukan', 'hilang'])
    ->whereNumber('page')
    ->middleware('throttle:catalog')
    ->name('laporan.search.index');
Route::get('/barang/{jenis}/halaman/{page}', [PublicLaporanController::class, 'page'])
    ->whereIn('jenis', ['ditemukan', 'hilang'])
    ->whereNumber('page')
    ->middleware('throttle:catalog')
    ->name('laporan.page');
Route::get('/barang/{jenis}', [PublicLaporanController::class, 'index'])
    ->whereIn('jenis', ['ditemukan', 'hilang'])
    ->middleware('throttle:catalog')
    ->name('laporan.index');

Route::view('/lapor', 'public.choose-report')->name('laporan.choose');
Route::get('/lapor/{jenis}', [PublicLaporanController::class, 'create'])
    ->whereIn('jenis', ['hilang', 'ditemukan'])->name('laporan.create');
Route::post('/lapor/{jenis}', [PublicLaporanController::class, 'store'])
    ->whereIn('jenis', ['hilang', 'ditemukan'])
    ->middleware('throttle:report-submit')
    ->name('laporan.store');
Route::get('/laporan/sukses/{kode}', [PublicLaporanController::class, 'confirmation'])
    ->middleware('throttle:report-detail')
    ->name('laporan.confirmation');
Route::get('/laporan/{laporan}', [PublicLaporanController::class, 'show'])
    ->middleware('throttle:report-detail')->name('laporan.show');
Route::get('/laporan/{laporan}/foto/{index}', [PublicLaporanController::class, 'foto'])
    ->where('index', '[0-2]')
    ->middleware('throttle:report-photo')
    ->name('foto.show');

Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [AuthController::class, 'create'])->name('login');
            Route::post('/login', [AuthController::class, 'store'])
                ->middleware('throttle:admin-login')
                ->name('login.submit');
        });

        Route::middleware(['auth:admin', 'auth.session'])->group(function () {
            Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
            Route::get('/', [AdminLaporanController::class, 'index'])->name('index');
            Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
            Route::middleware('throttle:admin-settings')->group(function () {
                Route::patch('/pengaturan/profil', [SettingsController::class, 'updateProfile'])->name('settings.profile');
                Route::put('/pengaturan/kata-sandi', [SettingsController::class, 'updatePassword'])->name('settings.password');
                Route::post('/pengaturan/admin', [SettingsController::class, 'storeAdmin'])->name('settings.admins.store');
            });
            Route::get('/laporan/{laporan}', [AdminLaporanController::class, 'show'])->name('show');
            Route::post('/laporan/{laporan}/review', [AdminLaporanController::class, 'review'])
                ->name('review');
            Route::post('/laporan/{laporan}/selesai', [AdminLaporanController::class, 'complete'])
                ->name('complete');
        });
    });
