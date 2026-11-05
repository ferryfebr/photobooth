<?php

namespace App\Modules\Photobooth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePhotoboothRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'frame_id' => ['required', 'integer', 'exists:frames,id'],
            'photos' => ['required', 'array', 'min:1', 'max:8'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'result' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:15360'],
            'device_fingerprint' => ['nullable', 'string', 'max:100'],
        ];
    }
}
