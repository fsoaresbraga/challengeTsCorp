<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;

/** @mixin \App\Models\ProposalAudit */
final class ProposalAuditResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proposal_id' => $this->proposal_id,
            'actor' => $this->actor,
            'event' => $this->event->value,
            'payload' => $this->payload,
            'created_at' => $this->formatDate($this->created_at),
        ];
    }
}
