<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\Enums\ProposalStatus;
use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

final class ListProposalsRequest extends ApiFormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(ProposalStatus::class)],
            'client_id' => ['sometimes', 'integer', 'exists:clients,id'],
            'product' => ['sometimes', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['sometimes', 'string', Rule::in(['created_at', 'monthly_value', 'product', 'status'])],
            'sort_direction' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
