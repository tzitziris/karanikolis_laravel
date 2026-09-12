<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleImageUploadRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alt_text' => ['nullable', 'string', 'max:190'],
            'photo' => ['required', 'file', 'max:'.(int) ceil((int) config('images.uploads.limits.max_bytes') / 1024)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'alt_text.max' => 'Η περιγραφή δεν μπορεί να ξεπερνά τους 190 χαρακτήρες.',
            'alt_text.string' => 'Η περιγραφή δεν είναι έγκυρη.',
            'photo.file' => 'Το αρχείο δεν είναι αποδεκτή φωτογραφία. Χρησιμοποιήστε JPEG ή PNG.',
            'photo.max' => 'Η φωτογραφία είναι πολύ μεγάλη. Ανεβάστε μικρότερο αρχείο.',
            'photo.required' => 'Διαλέξτε φωτογραφία για ανέβασμα.',
        ];
    }
}
