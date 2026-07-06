<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Exceptions\OptimisticLockException;
use App\Exceptions\StatusTransitionException;
use App\Models\Proposal;

final class ProposalStatusService
{
    /** @var array<string, list<string>> */
    private const ALLOWED_TRANSITIONS = [
        ProposalStatus::Draft->value => [
            ProposalStatus::Submitted->value,
            ProposalStatus::Canceled->value,
        ],
        ProposalStatus::Submitted->value => [
            ProposalStatus::Approved->value,
            ProposalStatus::Rejected->value,
            ProposalStatus::Canceled->value,
        ],
        ProposalStatus::Approved->value => [],
        ProposalStatus::Rejected->value => [],
        ProposalStatus::Canceled->value => [],
    ];

    public function assertVersion(Proposal $proposal, int $expectedVersion): void
    {
        if ($proposal->version !== $expectedVersion) {
            throw new OptimisticLockException();
        }
    }

    public function assertTransition(ProposalStatus $from, ProposalStatus $to): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$from->value] ?? [];

        if (! in_array($to->value, $allowed, true)) {
            throw new StatusTransitionException(
                sprintf('Invalid transition from %s to %s.', $from->value, $to->value),
            );
        }
    }
}
