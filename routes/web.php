<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Placeholder', [
        'message' => 'Η εφαρμογή Laravel είναι έτοιμη.',
    ]);
})->name('home');

Route::get('/dokimi-kinisis', function () {
    return Inertia::render('MotionDemo', [
        'message' => 'Η δοκιμαστική δεύτερη διαδρομή είναι έτοιμη.',
    ]);
})->name('motion-demo');
