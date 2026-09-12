<?php

namespace App\Http\Requests;

use App\Support\ArticleBodyValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ArticleContentRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'array'],
            'excerpt' => ['required', 'string', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'title' => ['required', 'string', 'max:190'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.array' => 'Το σώμα του άρθρου δεν είναι έγκυρο.',
            'body.required' => 'Γράψτε το κείμενο του άρθρου.',
            'excerpt.max' => 'Η σύνοψη δεν μπορεί να ξεπερνά τους 500 χαρακτήρες.',
            'excerpt.required' => 'Γράψτε τη σύνοψη.',
            'excerpt.string' => 'Η σύνοψη δεν είναι έγκυρη.',
            'published_at.date' => 'Η ημερομηνία δημοσίευσης δεν είναι έγκυρη.',
            'title.max' => 'Ο τίτλος δεν μπορεί να ξεπερνά τους 190 χαρακτήρες.',
            'title.required' => 'Γράψτε τον τίτλο.',
            'title.string' => 'Ο τίτλος δεν είναι έγκυρος.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $body = $this->input('body');

                if (! is_array($body)) {
                    return;
                }

                foreach (app(ArticleBodyValidator::class)->errors($body) as $message) {
                    $validator->errors()->add('body', $message);
                }
            },
        ];
    }
}
