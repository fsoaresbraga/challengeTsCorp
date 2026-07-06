<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalOrigin;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShowProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_proposal_by_id(): void
    {
        $proposal = Proposal::factory()->create([
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'status' => ProposalStatus::Draft,
            'origin' => ProposalOrigin::Api,
            'version' => 1,
        ]);

        $response = $this->getJson("/api/v1/proposals/{$proposal->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'client_id', 'product', 'monthly_value', 'status', 'origin', 'version', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.id', $proposal->id)
            ->assertJsonPath('data.product', 'Cloud Hosting Plan')
            ->assertJsonPath('data.monthly_value', '299.90')
            ->assertJsonPath('data.status', ProposalStatus::Draft->value)
            ->assertJsonPath('data.origin', ProposalOrigin::Api->value)
            ->assertJsonPath('data.version', 1);
    }

    public function test_returns_404_when_proposal_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/proposals/999');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_404_when_proposal_is_soft_deleted(): void
    {
        $proposal = Proposal::factory()->create();
        $proposal->delete();

        $response = $this->getJson("/api/v1/proposals/{$proposal->id}");

        $response->assertNotFound()
            ->assertJsonPath('message', 'Proposal not found.');
    }

    public function test_returns_422_when_id_is_invalid(): void
    {
        $response = $this->getJson('/api/v1/proposals/0');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['id']]);
    }
}
