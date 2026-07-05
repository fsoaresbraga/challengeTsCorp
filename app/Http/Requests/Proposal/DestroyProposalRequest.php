<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\Http\Requests\ApiFormRequest;

final class DestroyProposalRequest extends ApiFormRequest
{
    /** @return array<string, mixed> */
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'version' => ['required', 'integer', 'min:1'],
        ];
    }
}
