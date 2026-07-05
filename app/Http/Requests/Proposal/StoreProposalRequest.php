<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\Enums\ProposalOrigin;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

final class StoreProposalRequest extends ApiFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'product' => ['required', 'string', 'max:255'],
            'monthly_value' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'origin' => ['required', Rule::enum(ProposalOrigin::class)],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'client_id.required' => 'The client_id field is required.',
            'client_id.exists' => 'The selected client does not exist.',
            'origin.required' => 'The origin field is required.',
            'idempotency_key.required' => 'The Idempotency-Key header is required.',
        ];
    }
}
