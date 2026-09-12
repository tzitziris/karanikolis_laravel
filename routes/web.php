<?php

use App\Http\Controllers\AdminArticleContentController;
use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\AdminArticleImageController;
use App\Http\Controllers\AdminArticlePublicationController;
use App\Http\Controllers\AdminDashboardController;
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
    Route::get('/', AdminDashboardController::class)->name('home');
    Route::get('/articles/create', [AdminArticleContentController::class, 'create'])
        ->name('articles.create');
    Route::post('/articles', [AdminArticleContentController::class, 'store'])
        ->name('articles.store');
    Route::get('/articles/{article}/edit', [AdminArticleContentController::class, 'edit'])
        ->name('articles.edit');
    Route::put('/articles/{article}', [AdminArticleContentController::class, 'update'])
        ->name('articles.update');
    Route::post('/articles/{article}/cover', [AdminArticleImageController::class, 'storeCover'])
        ->name('articles.cover.store');
    Route::delete('/articles/{article}/cover', [AdminArticleImageController::class, 'destroyCover'])
        ->name('articles.cover.destroy');
    Route::post('/articles/{article}/gallery', [AdminArticleImageController::class, 'storeGallery'])
        ->name('articles.gallery.store');
    Route::put('/articles/{article}/gallery', [AdminArticleImageController::class, 'updateGallery'])
        ->name('articles.gallery.update');
    Route::delete('/articles/{article}/gallery/{image}', [AdminArticleImageController::class, 'destroyGallery'])
        ->name('articles.gallery.destroy');
    Route::patch('/articles/{article}/publish', [AdminArticlePublicationController::class, 'publish'])
        ->name('articles.publish');
    Route::patch('/articles/{article}/unpublish', [AdminArticlePublicationController::class, 'unpublish'])
        ->name('articles.unpublish');
    Route::delete('/articles/{article}', [AdminArticleController::class, 'destroy'])
        ->name('articles.destroy');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::any('/{adminPath}', fn () => abort(404))
        ->where('adminPath', '.*')
        ->name('missing');
});

Route::fallback(NotFoundController::class);
