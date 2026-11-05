<?php

namespace App\Modules\Core\Http\Requests;

use App\Modules\Core\Models\EventModule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
            'name' => ['required', 'string', 'max:255'],
            'theme_slug' => ['nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date'],
            'photobooth_active_from' => ['nullable', 'date'],
            'photobooth_active_until' => ['nullable', 'date', 'after_or_equal:photobooth_active_from'],
            'storage_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'status' => ['required', Rule::in(['draft', 'active', 'completed', 'archived'])],
            'modules' => ['nullable', 'array'],
            'modules.*' => [Rule::in(EventModule::MODULE_KEYS)],
        ];
    }
}
