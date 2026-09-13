<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleVideoOrderRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'videos' => ['present', 'array'],
            'videos.*.id' => ['required', 'integer'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'videos.array' => 'Η σειρά των βίντεο δεν είναι έγκυρη.',
            'videos.present' => 'Η σειρά των βίντεο λείπει.',
            'videos.*.id.integer' => 'Το βίντεο δεν είναι έγκυρο.',
            'videos.*.id.required' => 'Το βίντεο δεν είναι έγκυρο.',
        ];
    }
}
