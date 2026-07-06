<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProposalAuditEvent;
use App\Exceptions\EntityNotFoundException;
use App\Models\Proposal;
use App\Models\ProposalAudit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProposalAuditService
{
    private const DEFAULT_PER_PAGE = 15;

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

    /**
     * @param array{page?: int, per_page?: int} $filters
     */
    public function listForProposal(int $proposalId, array $filters = []): LengthAwarePaginator
    {
        $proposal = Proposal::query()->withTrashed()->find($proposalId);
        if ($proposal === null) {
            throw new EntityNotFoundException('Proposal');
        }

        return ProposalAudit::query()
            ->where('proposal_id', $proposalId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate(
                perPage: $filters['per_page'] ?? self::DEFAULT_PER_PAGE,
                page: $filters['page'] ?? null,
            );
    }
}
