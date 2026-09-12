<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsArchiveController;
use App\Http\Controllers\NewsArticleController;
use App\Http\Controllers\NotFoundController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

Route::get('/coaches', function () {
    return Inertia::render('Coaches');
})->name('coaches');

Route::get('/schedule', function () {
    return Inertia::render('Schedule');
})->name('schedule');

Route::get('/news', NewsArchiveController::class)->name('news');
Route::get('/news/{slug}', NewsArticleController::class)->name('news.show');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', fn () => Inertia::render('Admin/Login'))
        ->name('login');
});

Route::post('/admin/login', [LoginController::class, 'store'])
    ->middleware('guest')
    ->name('admin.login.store');

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => Inertia::render('Admin/SignedIn'))->name('home');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::any('/{adminPath}', fn () => abort(404))
        ->where('adminPath', '.*')
        ->name('missing');
});

Route::fallback(NotFoundController::class);
