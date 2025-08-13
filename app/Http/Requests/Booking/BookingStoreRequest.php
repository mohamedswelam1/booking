<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class BookingStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user() && $this->user()->role === 'customer');
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'string', 'exists:services,id'],
            'start_time' => ['required', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.exists' => 'The selected service does not exist or is not available.',
            'start_time.after' => 'Booking time must be in the future.',
        ];
    }
}


