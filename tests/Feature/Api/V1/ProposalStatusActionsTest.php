<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalAuditEvent;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProposalStatusActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_submits_draft_proposal(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);

        $response = $this->postJson("/api/v1/proposals/{$proposal->id}/submit", [
            'version' => 1,
        ], [
            'Idempotency-Key' => 'submit-key-001',
            'X-Actor' => 'user:11',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', ProposalStatus::Submitted->value)
            ->assertJsonPath('data.version', 2);

        $this->assertDatabaseHas('proposal_audits', [
            'proposal_id' => $proposal->id,
            'actor' => 'user:11',
            'event' => ProposalAuditEvent::StatusChanged->value,
        ]);
    }

    public function test_replays_submit_with_same_idempotency_key(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);
        $headers = ['Idempotency-Key' => 'submit-key-002'];

        $first = $this->postJson("/api/v1/proposals/{$proposal->id}/submit", ['version' => 1], $headers);
        $second = $this->postJson("/api/v1/proposals/{$proposal->id}/submit", ['version' => 1], $headers);

        $first->assertOk();
        $second->assertOk()
            ->assertJsonPath('data.id', $first->json('data.id'))
            ->assertJsonPath('data.status', ProposalStatus::Submitted->value);
    }

    public function test_approve_reject_and_cancel_transitions(): void
    {
        $submitted = Proposal::factory()->submitted()->create(['version' => 2]);

        $this->postJson("/api/v1/proposals/{$submitted->id}/approve", ['version' => 2])
            ->assertOk()
            ->assertJsonPath('data.status', ProposalStatus::Approved->value);

        $submitted2 = Proposal::factory()->submitted()->create(['version' => 2]);

        $this->postJson("/api/v1/proposals/{$submitted2->id}/reject", ['version' => 2])
            ->assertOk()
            ->assertJsonPath('data.status', ProposalStatus::Rejected->value);

        $draft = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);

        $this->postJson("/api/v1/proposals/{$draft->id}/cancel", ['version' => 1])
            ->assertOk()
            ->assertJsonPath('data.status', ProposalStatus::Canceled->value);
    }

    public function test_returns_422_for_invalid_transition(): void
    {
        $proposal = Proposal::factory()->approved()->create(['version' => 3]);

        $this->postJson("/api/v1/proposals/{$proposal->id}/cancel", ['version' => 3])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid transition from APPROVED to CANCELED.');
    }

    public function test_returns_409_for_status_action_version_conflict(): void
    {
        $proposal = Proposal::factory()->submitted()->create(['version' => 3]);

        $this->postJson("/api/v1/proposals/{$proposal->id}/approve", ['version' => 2])
            ->assertConflict()
            ->assertJsonPath('message', 'Version conflict.');
    }

    public function test_submit_requires_idempotency_header(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);

        $this->postJson("/api/v1/proposals/{$proposal->id}/submit", ['version' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('errors.idempotency_key.0', 'The Idempotency-Key header is required.');
    }

    public function test_returns_422_when_approving_draft_proposal(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);

        $this->postJson("/api/v1/proposals/{$proposal->id}/approve", ['version' => 1])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Invalid transition from DRAFT to APPROVED.');
    }

    public function test_cancels_submitted_proposal(): void
    {
        $proposal = Proposal::factory()->submitted()->create(['version' => 2]);

        $this->postJson("/api/v1/proposals/{$proposal->id}/cancel", ['version' => 2])
            ->assertOk()
            ->assertJsonPath('data.status', ProposalStatus::Canceled->value)
            ->assertJsonPath('data.version', 3);
    }

    public function test_returns_404_when_proposal_does_not_exist_on_submit(): void
    {
        $this->postJson('/api/v1/proposals/999/submit', ['version' => 1], [
            'Idempotency-Key' => 'submit-key-404',
        ])
            ->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_422_when_submit_idempotency_key_is_reused_with_different_payload(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);
        $headers = ['Idempotency-Key' => 'submit-key-conflict'];

        $this->postJson("/api/v1/proposals/{$proposal->id}/submit", ['version' => 1], $headers)
            ->assertOk();

        $this->postJson("/api/v1/proposals/{$proposal->id}/submit", ['version' => 2], $headers)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Idempotency key reused with a different request payload.');
    }
}
