<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Placeholder', [
        'message' => 'Η εφαρμογή Laravel είναι έτοιμη.',
    ]);
});
