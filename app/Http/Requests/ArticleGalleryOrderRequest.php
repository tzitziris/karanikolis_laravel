<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleGalleryOrderRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'images' => ['present', 'array'],
            'images.*.alt_text' => ['nullable', 'string', 'max:190'],
            'images.*.id' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'images.array' => 'Η σειρά των φωτογραφιών δεν είναι έγκυρη.',
            'images.present' => 'Η σειρά των φωτογραφιών λείπει.',
            'images.*.alt_text.max' => 'Η περιγραφή δεν μπορεί να ξεπερνά τους 190 χαρακτήρες.',
            'images.*.alt_text.string' => 'Η περιγραφή δεν είναι έγκυρη.',
            'images.*.id.integer' => 'Η φωτογραφία δεν είναι έγκυρη.',
            'images.*.id.required' => 'Η φωτογραφία δεν είναι έγκυρη.',
        ];
    }
}
