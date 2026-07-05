<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\Http\Requests\ApiFormRequest;

final class ProposalStatusActionRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [
            'id' => $this->route('id'),
            'actor' => $this->header('X-Actor'),
        ];

        if ($this->route()->named('v1.proposals.submit')) {
            $merge['idempotency_key'] = $this->header('Idempotency-Key');
        }

        $this->merge($merge);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'id' => ['required', 'integer', 'min:1'],
            'version' => ['required', 'integer', 'min:1'],
            'actor' => ['nullable', 'string', 'max:100', 'regex:/^(system|user:\d+)$/'],
        ];

        if ($this->route()->named('v1.proposals.submit')) {
            $rules['idempotency_key'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'version.required' => 'The version field is required for optimistic lock.',
            'idempotency_key.required' => 'The Idempotency-Key header is required.',
            'actor.regex' => 'The actor must be "system" or "user:{id}".',
        ];
    }
}
