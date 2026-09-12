<?php

namespace App\Http\Requests;

use App\Support\UploadLimits;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class ArticleImageUploadRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alt_text' => ['nullable', 'string', 'max:190'],
            'photo' => ['required', 'file', 'max:'.UploadLimits::articleImageMaxKilobytes()],
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
            'photo.max' => 'Η φωτογραφία είναι πολύ μεγάλη. Το όριο είναι '.UploadLimits::articleImageMaxLabel().'.',
            'photo.required' => 'Διαλέξτε φωτογραφία για ανέβασμα.',
            'photo.uploaded' => 'Η φωτογραφία δεν ανέβηκε. Το όριο είναι '.UploadLimits::articleImageMaxLabel().'.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $message = $this->failedUploadMessage();

        if ($message !== null) {
            throw ValidationException::withMessages([
                'photo' => $message,
            ])
                ->errorBag($this->errorBag)
                ->redirectTo($this->getRedirectUrl());
        }

        parent::failedValidation($validator);
    }

    private function failedUploadMessage(): ?string
    {
        $file = $this->file('photo');

        if (! $file instanceof UploadedFile || $file->isValid()) {
            return null;
        }

        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Η φωτογραφία είναι μεγαλύτερη από όσο δέχεται ο server. Το όριο είναι '.UploadLimits::articleImageMaxLabel().'.',
            UPLOAD_ERR_PARTIAL => 'Το ανέβασμα της φωτογραφίας διακόπηκε. Δοκιμάστε ξανά.',
            UPLOAD_ERR_NO_FILE => 'Διαλέξτε φωτογραφία για ανέβασμα.',
            UPLOAD_ERR_NO_TMP_DIR => 'Ο server δεν έχει διαθέσιμο προσωρινό φάκελο για το ανέβασμα. Ζητήστε τεχνικό έλεγχο.',
            UPLOAD_ERR_CANT_WRITE => 'Ο server δεν μπόρεσε να γράψει τη φωτογραφία στο δίσκο. Ζητήστε τεχνικό έλεγχο.',
            UPLOAD_ERR_EXTENSION => 'Το ανέβασμα της φωτογραφίας σταμάτησε από επέκταση του server. Ζητήστε τεχνικό έλεγχο.',
            default => 'Η φωτογραφία δεν ανέβηκε. Δοκιμάστε ξανά.',
        };
    }
}
