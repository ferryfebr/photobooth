<?php

namespace App\Modules\GuestPhotographer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestPhotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'device_fingerprint' => ['nullable', 'string', 'max:100'],
        ];
    }
}
