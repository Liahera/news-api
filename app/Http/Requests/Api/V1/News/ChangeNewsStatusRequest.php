<?php

namespace App\Http\Requests\Api\V1\News;

use Illuminate\Foundation\Http\FormRequest;

class ChangeNewsStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'is_published' => ['required', 'boolean'],
        ];
    }
}
