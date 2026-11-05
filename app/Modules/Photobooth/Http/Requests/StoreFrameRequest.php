<?php

namespace App\Modules\Photobooth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFrameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $photoCount = (int) $this->input('photo_count', 1);

        return [
            'name' => ['required', 'string', 'max:255'],
            'photo_count' => ['required', 'integer', 'min:1', 'max:8'],
            'slots' => ['required', 'array', 'size:'.max(1, $photoCount)],
            'slots.*.x' => ['required', 'numeric', 'min:0'],
            'slots.*.y' => ['required', 'numeric', 'min:0'],
            'slots.*.width' => ['required', 'numeric', 'min:1'],
            'slots.*.height' => ['required', 'numeric', 'min:1'],
            'frame' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:png', 'max:10240'],
        ];
    }
}
