<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProposalAuditEvent;
use App\Models\Proposal;
use App\Models\ProposalAudit;

final class ProposalAuditService
{
    public function resolveActor(?string $actorHeader): string
    {
        if ($actorHeader === null || $actorHeader === '') {
            return 'system';
        }

        return $actorHeader;
    }

    /** @param array<string, mixed> $payload */
    public function record(
        Proposal $proposal,
        string $actor,
        ProposalAuditEvent $event,
        array $payload,
    ): ProposalAudit {
        return ProposalAudit::query()->create([
            'proposal_id' => $proposal->id,
            'actor' => $actor,
            'event' => $event,
            'payload' => $payload,
        ]);
    }
}
