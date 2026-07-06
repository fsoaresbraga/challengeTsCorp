<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalAuditEvent;
use App\Models\Proposal;
use App\Models\ProposalAudit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListProposalAuditsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_paginated_audit_trail(): void
    {
        $proposal = Proposal::factory()->create();

        ProposalAudit::factory()->for($proposal)->create([
            'event' => ProposalAuditEvent::Created,
            'actor' => 'system',
            'payload' => ['product' => 'Plan A'],
            'created_at' => now()->subMinutes(2),
        ]);
        ProposalAudit::factory()->for($proposal)->create([
            'event' => ProposalAuditEvent::StatusChanged,
            'actor' => 'user:1',
            'payload' => ['from' => 'DRAFT', 'to' => 'SUBMITTED'],
            'created_at' => now()->subMinute(),
        ]);

        $response = $this->getJson("/api/v1/proposals/{$proposal->id}/audit");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'proposal_id', 'actor', 'event', 'payload', 'created_at'],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ])
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.event', ProposalAuditEvent::Created->value)
            ->assertJsonPath('data.0.actor', 'system')
            ->assertJsonPath('data.1.event', ProposalAuditEvent::StatusChanged->value)
            ->assertJsonPath('data.1.actor', 'user:1');
    }

    public function test_respects_per_page_and_page(): void
    {
        $proposal = Proposal::factory()->create();
        ProposalAudit::factory()->for($proposal)->count(5)->create();

        $response = $this->getJson("/api/v1/proposals/{$proposal->id}/audit?per_page=2&page=2");

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonCount(2, 'data');
    }

    public function test_returns_audits_for_soft_deleted_proposal(): void
    {
        $proposal = Proposal::factory()->create();
        ProposalAudit::factory()->for($proposal)->create([
            'event' => ProposalAuditEvent::DeletedLogical,
            'payload' => ['status' => 'DRAFT'],
        ]);
        $proposal->delete();

        $response = $this->getJson("/api/v1/proposals/{$proposal->id}/audit");

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.event', ProposalAuditEvent::DeletedLogical->value);
    }

    public function test_returns_404_when_proposal_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/proposals/999/audit');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_422_when_id_is_invalid(): void
    {
        $response = $this->getJson('/api/v1/proposals/0/audit');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['id']]);
    }
}
