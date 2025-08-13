<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user() && $this->user()->role === 'admin');
    }

    public function rules(): array
    {
        return [
            'provider_id' => ['sometimes', 'string'],
            'service_id' => ['sometimes', 'string'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
        ];
    }
}


