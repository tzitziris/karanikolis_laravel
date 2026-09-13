@extends('errors.layout')
@section('eyebrow', 'Σφάλμα 413')
@section('title', 'Η φωτογραφία είναι πολύ μεγάλη')
@section('detail', 'Η φωτογραφία είναι μεγαλύτερη από όσο δέχεται ο server. Ανεβάστε μικρότερη φωτογραφία. Το όριο είναι '.$maxLabel.'.')
@section('action')
    <a class="action" href="/admin">Πίνακας διαχείρισης</a>
@endsection
