<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\Proposal */
final class ProposalResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'product' => $this->product,
            'monthly_value' => (string) $this->monthly_value,
            'status' => $this->status->value,
            'origin' => $this->origin->value,
            'version' => $this->version,
            'created_at' => $this->formatDate($this->created_at),
            'updated_at' => $this->formatDate($this->updated_at),
        ];
    }
}
