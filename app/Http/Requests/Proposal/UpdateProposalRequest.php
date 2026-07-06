<?php

declare(strict_types=1);

namespace App\Http\Requests\Proposal;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;

final class UpdateProposalRequest extends ApiFormRequest
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
            'product' => ['sometimes', 'string', 'max:255'],
            'monthly_value' => ['sometimes', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->has('product') && ! $this->has('monthly_value')) {
                $validator->errors()->add(
                    'product',
                    'At least one of product or monthly_value must be provided.',
                );
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'version.required' => 'The version field is required for optimistic lock.',
        ];
    }
}
