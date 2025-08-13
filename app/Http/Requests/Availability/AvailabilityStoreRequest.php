<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class AvailabilityStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user() && $this->user()->role === 'provider');
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'entries.*.start_time' => ['required', 'date_format:H:i'],
            'entries.*.end_time' => ['required', 'date_format:H:i', 'after:entries.*.start_time'],
            'entries.*.timezone' => ['required', 'string'],
        ];
    }
}


