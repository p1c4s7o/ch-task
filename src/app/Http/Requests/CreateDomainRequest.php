<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string[]>
     */
    public function rules(): array
    {
        return [
            'domain' => [
                'required', 'string',
                'min:3', 'max:255',
                'regex:/^[a-z0-9]+([a-z0-9-]+)?$/i',
            ],
            'port' => [
                'nullable', 'integer',
                'min:1', 'max:65535',
            ],
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'domain.regex' => 'Domain may contain only letters, numbers and hyphen.',
        ];
    }
}
