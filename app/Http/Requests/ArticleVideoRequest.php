<?php

namespace App\Http\Requests;

use App\Support\YouTubeVideo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ArticleVideoRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'youtube_url' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'youtube_url.max' => 'Ο σύνδεσμος είναι πολύ μεγάλος.',
            'youtube_url.required' => 'Επικολλήστε τον σύνδεσμο του βίντεο.',
            'youtube_url.string' => 'Ο σύνδεσμος δεν είναι έγκυρος.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $url = $this->input('youtube_url');

                if (! is_string($url) || $url === '') {
                    return;
                }

                // A link we cannot read an id from would be stored with an empty
                // youtube_id and silently render nothing on the article page.
                if (YouTubeVideo::idFromUrl($url) === null) {
                    $validator->errors()->add(
                        'youtube_url',
                        'Ο σύνδεσμος δεν είναι βίντεο YouTube. Αντιγράψτε τη διεύθυνση από το YouTube.',
                    );
                }
            },
        ];
    }
}
