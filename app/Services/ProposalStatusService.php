<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\OptimisticLockException;
use App\Models\Proposal;

final class ProposalStatusService
{
    public function assertVersion(Proposal $proposal, int $expectedVersion): void
    {
        if ($proposal->version !== $expectedVersion) {
            throw new OptimisticLockException();
        }
    }
}
