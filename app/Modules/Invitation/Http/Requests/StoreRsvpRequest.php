<?php

namespace App\Modules\Invitation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:255'],
            'attendance' => ['required', 'in:hadir,tidak_hadir'],
            'guest_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'note' => ['nullable', 'string', 'max:2000'],
            'device_fingerprint' => ['nullable', 'string', 'max:100'],
        ];
    }
}
