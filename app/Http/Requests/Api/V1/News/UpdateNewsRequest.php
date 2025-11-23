<?php

namespace App\Http\Requests\Api\V1\News;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'             => ['sometimes', 'string', 'max:255'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'image'             => ['sometimes', 'nullable', 'image'],
            'is_published'      => ['sometimes', 'boolean'],
            'published_at'      => ['sometimes', 'nullable', 'date'],
            'blocks'            => ['sometimes', 'array'],
            'blocks.*.type'     => ['required_with:blocks', 'in:text,image,text_image_left,text_image_right'],
            'blocks.*.text'     => ['nullable', 'string'],
            'blocks.*.position' => ['nullable', 'integer'],
        ];
    }
}
