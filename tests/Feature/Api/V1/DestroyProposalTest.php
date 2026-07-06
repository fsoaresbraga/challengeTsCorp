<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalAuditEvent;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DestroyProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deletes_draft_proposal(): void
    {
        $proposal = Proposal::factory()->create([
            'status' => ProposalStatus::Draft,
            'version' => 1,
        ]);

        $response = $this->deleteJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
        ], [
            'X-Actor' => 'user:7',
        ]);

        $response->assertNoContent();

        $this->assertSoftDeleted('proposals', ['id' => $proposal->id]);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'version' => 2,
        ]);

        $this->assertDatabaseHas('proposal_audits', [
            'proposal_id' => $proposal->id,
            'actor' => 'user:7',
            'event' => ProposalAuditEvent::DeletedLogical->value,
        ]);
    }

    public function test_soft_deletes_submitted_proposal(): void
    {
        $proposal = Proposal::factory()->submitted()->create(['version' => 1]);

        $response = $this->deleteJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
        ]);

        $response->assertNoContent();
        $this->assertSoftDeleted('proposals', ['id' => $proposal->id]);
    }

    public function test_returns_422_when_proposal_cannot_be_deleted_in_current_status(): void
    {
        $proposal = Proposal::factory()->approved()->create(['version' => 1]);

        $response = $this->deleteJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Proposal cannot be deleted in current status.');

        $this->assertNotSoftDeleted('proposals', ['id' => $proposal->id]);
    }

    public function test_returns_409_when_version_conflicts(): void
    {
        $proposal = Proposal::factory()->create([
            'status' => ProposalStatus::Draft,
            'version' => 2,
        ]);

        $response = $this->deleteJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
        ]);

        $response->assertConflict()
            ->assertJsonPath('message', 'Version conflict.');

        $this->assertNotSoftDeleted('proposals', ['id' => $proposal->id]);
    }

    public function test_returns_404_when_proposal_does_not_exist(): void
    {
        $response = $this->deleteJson('/api/v1/proposals/999', [
            'version' => 1,
        ]);

        $response->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_422_when_version_is_missing(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft]);

        $response = $this->deleteJson("/api/v1/proposals/{$proposal->id}", []);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['version']]);
    }
}
