<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProposalAuditEvent;
use App\Models\Proposal;
use App\Models\ProposalAudit;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProposalAudit> */
final class ProposalAuditFactory extends Factory
{
    protected $model = ProposalAudit::class;

    public function definition(): array
    {
        return [
            'proposal_id' => Proposal::factory(),
            'actor' => 'system',
            'event' => ProposalAuditEvent::Created,
            'payload' => ['source' => 'factory'],
        ];
    }
}
