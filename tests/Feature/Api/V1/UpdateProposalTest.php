<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalAuditEvent;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_proposal_fields_and_increments_version(): void
    {
        $proposal = Proposal::factory()->create([
            'product' => 'Basic Plan',
            'monthly_value' => 199.90,
            'status' => ProposalStatus::Draft,
            'version' => 1,
        ]);

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
            'product' => 'Premium Plan',
            'monthly_value' => 299.90,
        ], [
            'X-Actor' => 'user:10',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.product', 'Premium Plan')
            ->assertJsonPath('data.monthly_value', '299.90')
            ->assertJsonPath('data.version', 2);

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'product' => 'Premium Plan',
            'monthly_value' => '299.90',
            'version' => 2,
        ]);

        $this->assertDatabaseHas('proposal_audits', [
            'proposal_id' => $proposal->id,
            'actor' => 'user:10',
            'event' => ProposalAuditEvent::UpdatedFields->value,
        ]);
    }

    public function test_returns_409_when_version_conflicts(): void
    {
        $proposal = Proposal::factory()->create([
            'status' => ProposalStatus::Draft,
            'version' => 2,
        ]);

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
            'product' => 'Updated Plan',
        ]);

        $response->assertConflict()
            ->assertJsonPath('message', 'Version conflict.');

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'version' => 2,
        ]);
    }

    public function test_returns_422_when_proposal_is_not_in_draft_status(): void
    {
        $proposal = Proposal::factory()->submitted()->create(['version' => 1]);

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
            'product' => 'Updated Plan',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Proposal cannot be updated in current status.');
    }

    public function test_returns_404_when_proposal_does_not_exist(): void
    {
        $response = $this->patchJson('/api/v1/proposals/999', [
            'version' => 1,
            'product' => 'Updated Plan',
        ]);

        $response->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_422_when_version_is_missing(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft]);

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'product' => 'Updated Plan',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['version']]);
    }

    public function test_returns_422_when_no_updatable_fields_are_provided(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonPath('errors.product.0', 'At least one of product or monthly_value must be provided.');
    }

    public function test_returns_404_when_proposal_is_soft_deleted(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);
        $proposal->delete();

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
            'product' => 'Updated Plan',
        ]);

        $response->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_422_when_actor_header_is_invalid(): void
    {
        $proposal = Proposal::factory()->create(['status' => ProposalStatus::Draft, 'version' => 1]);

        $response = $this->patchJson("/api/v1/proposals/{$proposal->id}", [
            'version' => 1,
            'product' => 'Updated Plan',
        ], [
            'X-Actor' => 'bad-actor',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'The actor must be "system" or "user:{id}".');
    }
}
