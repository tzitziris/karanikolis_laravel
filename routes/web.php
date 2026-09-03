<?php

use App\Support\ReadablePage;
use Illuminate\Support\Facades\Route;

$placeholderFallback = fn (string $message): array => [
    'actions' => [
        [
            'href' => route('home'),
            'label' => 'Αρχική δοκιμή',
        ],
        [
            'href' => route('motion-demo'),
            'label' => 'Δοκιμή κίνησης',
        ],
    ],
    'eyebrow' => 'Βήμα θεμελίωσης',
    'sections' => [
        [
            'body' => 'Το περιεχόμενο υπάρχει κανονικά πριν ξεκινήσει οποιαδήποτε κίνηση.',
            'title' => 'Πρώτη ανάγνωση',
        ],
        [
            'body' => 'Τα στοιχεία μπορούν να εμφανίζονται διακριτικά όταν μπαίνουν στο οπτικό πεδίο.',
            'title' => 'Κύλιση',
        ],
        [
            'body' => 'Κάθε ενεργή κίνηση ανήκει στη σελίδα που τη δημιούργησε και καθαρίζεται στην αλλαγή σελίδας.',
            'title' => 'Καθαρό κλείσιμο',
        ],
    ],
    'summary' => $message,
    'title' => 'Προσωρινή σελίδα',
];

Route::get('/', function () use ($placeholderFallback) {
    return ReadablePage::render('Placeholder', [
        'message' => 'Η εφαρμογή Laravel είναι έτοιμη.',
    ], $placeholderFallback('Η εφαρμογή Laravel είναι έτοιμη.'));
})->name('home');

Route::get('/dokimi-kinisis', function () use ($placeholderFallback) {
    return ReadablePage::render('Placeholder', [
        'message' => 'Η δοκιμαστική δεύτερη διαδρομή είναι έτοιμη.',
    ], $placeholderFallback('Η δοκιμαστική δεύτερη διαδρομή είναι έτοιμη.'));
})->name('motion-demo');
