<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Placeholder', [
        'message' => 'Η εφαρμογή Laravel είναι έτοιμη.',
    ]);
})->name('home');

Route::get('/coaches', function () {
    return Inertia::render('PublicPlaceholder', [
        'eyebrow' => 'Προσωρινή σελίδα',
        'message' => 'Η πραγματική σελίδα της ομάδας θα προστεθεί σε επόμενο βήμα.',
        'title' => 'Ομάδα',
    ]);
})->name('coaches');

Route::get('/schedule', function () {
    return Inertia::render('PublicPlaceholder', [
        'eyebrow' => 'Προσωρινή σελίδα',
        'message' => 'Το πραγματικό πρόγραμμα προπονήσεων θα προστεθεί σε επόμενο βήμα.',
        'title' => 'Πρόγραμμα',
    ]);
})->name('schedule');

Route::get('/news', function () {
    return Inertia::render('PublicPlaceholder', [
        'eyebrow' => 'Προσωρινή σελίδα',
        'message' => 'Η πραγματική σελίδα νέων θα προστεθεί σε επόμενο βήμα.',
        'title' => 'Νέα',
    ]);
})->name('news');

Route::get('/about', function () {
    return Inertia::render('PublicPlaceholder', [
        'eyebrow' => 'Προσωρινή σελίδα',
        'message' => 'Η πραγματική σελίδα σχετικά με τη σχολή θα προστεθεί σε επόμενο βήμα.',
        'title' => 'Σχετικά',
    ]);
})->name('about');

Route::get('/dokimi-kinisis', function () {
    return Inertia::render('MotionDemo', [
        'message' => 'Η δοκιμαστική δεύτερη διαδρομή είναι έτοιμη.',
    ]);
})->name('motion-demo');
