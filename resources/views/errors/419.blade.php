@extends('errors.layout')
@section('eyebrow', 'Σφάλμα 419')
@section('title', 'Η σελίδα έληξε')
@section('detail', 'Η φόρμα ήταν ανοιχτή για πολλή ώρα και δεν στάλθηκε. Δεν χάθηκε τίποτα από όσα είχατε αποθηκεύσει. Συνδεθείτε ξανά και δοκιμάστε το.')
@section('action')
    <a class="action" href="/admin">Πίνακας διαχείρισης</a>
@endsection
