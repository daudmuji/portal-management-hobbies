<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $hobbies = collect($this->input('hobbies', []))
            ->filter(fn ($h) => is_array($h) && filled($h['name'] ?? null))
            ->values()
            ->all();

        $this->merge(['hobbies' => $hobbies]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'regex:/^[a-zA-Z\s]+$/u', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'hobbies' => ['nullable', 'array'],
            'hobbies.*.id' => ['nullable', 'integer'],
            'hobbies.*.name' => ['required', 'regex:/^[a-zA-Z\s]+$/u', 'max:100'],
        ];
    }
}
