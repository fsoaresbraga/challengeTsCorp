<?php

declare(strict_types=1);

namespace App\Http\Requests\Client;

use App\Http\Requests\ApiFormRequest;
use App\Rules\BrazilianDocument;
use Illuminate\Validation\Rule;

final class StoreClientRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('document')) {
            $this->merge([
                'document' => strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $this->input('document')) ?? ''),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('clients', 'email')],
            'document' => ['required', 'string', new BrazilianDocument(), Rule::unique('clients', 'document')],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'document.required' => 'The document field is required.',
            'document.unique' => 'The document has already been taken.',
        ];
    }
}
