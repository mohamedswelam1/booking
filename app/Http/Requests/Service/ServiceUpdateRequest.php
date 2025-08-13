<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user() && $this->user()->role === 'provider');
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'string', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration' => ['sometimes', 'integer', 'min:1', 'max:1440'], // max 24 hours
            'price' => ['sometimes', 'numeric', 'min:0', 'max:999999.99'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist. Please choose a valid category.',
            'duration.max' => 'Service duration cannot exceed 24 hours (1440 minutes).',
            'price.max' => 'Service price cannot exceed 999,999.99.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }
}


