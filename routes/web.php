<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsArchiveController;
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

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');
