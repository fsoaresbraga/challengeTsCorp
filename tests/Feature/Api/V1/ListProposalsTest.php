<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ListProposalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_paginated_proposals(): void
    {
        Proposal::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/proposals');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'client_id', 'product', 'monthly_value', 'status', 'origin', 'version', 'created_at', 'updated_at'],
                ],
                'links' => ['first', 'last', 'prev', 'next'],
                'meta' => ['current_page', 'from', 'last_page', 'per_page', 'to', 'total'],
            ])
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonCount(15, 'data');
    }

    public function test_filters_by_status(): void
    {
        Proposal::factory()->count(2)->create(['status' => ProposalStatus::Draft]);
        Proposal::factory()->submitted()->count(3)->create();

        $response = $this->getJson('/api/v1/proposals?status=SUBMITTED');

        $response->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonCount(3, 'data');

        foreach ($response->json('data') as $proposal) {
            $this->assertSame(ProposalStatus::Submitted->value, $proposal['status']);
        }
    }

    public function test_filters_by_client_id(): void
    {
        $client = Client::factory()->create();
        $otherClient = Client::factory()->create();

        Proposal::factory()->count(2)->create(['client_id' => $client->id]);
        Proposal::factory()->create(['client_id' => $otherClient->id]);

        $response = $this->getJson("/api/v1/proposals?client_id={$client->id}");

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $proposal) {
            $this->assertSame($client->id, $proposal['client_id']);
        }
    }

    public function test_filters_by_product(): void
    {
        Proposal::factory()->create(['product' => 'Cloud Hosting Plan']);
        Proposal::factory()->create(['product' => 'Dedicated Server']);
        Proposal::factory()->create(['product' => 'Cloud Backup']);

        $response = $this->getJson('/api/v1/proposals?product=Cloud');

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonCount(2, 'data');
    }

    public function test_sorts_by_monthly_value_ascending(): void
    {
        Proposal::factory()->create(['monthly_value' => 500.00]);
        Proposal::factory()->create(['monthly_value' => 100.00]);
        Proposal::factory()->create(['monthly_value' => 300.00]);

        $response = $this->getJson('/api/v1/proposals?sort_by=monthly_value&sort_direction=asc');

        $response->assertOk();
        $values = array_column($response->json('data'), 'monthly_value');
        $this->assertSame(['100.00', '300.00', '500.00'], $values);
    }

    public function test_respects_per_page_and_page(): void
    {
        Proposal::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/proposals?per_page=2&page=2');

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonCount(2, 'data');
    }

    public function test_excludes_soft_deleted_proposals(): void
    {
        $active = Proposal::factory()->create();
        $deleted = Proposal::factory()->create();
        $deleted->delete();

        $response = $this->getJson('/api/v1/proposals');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $active->id);
    }

    public function test_returns_422_for_invalid_status(): void
    {
        $response = $this->getJson('/api/v1/proposals?status=INVALID');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_returns_422_for_nonexistent_client_id(): void
    {
        $response = $this->getJson('/api/v1/proposals?client_id=999');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['client_id']]);
    }

    public function test_returns_422_for_invalid_sort_by(): void
    {
        $response = $this->getJson('/api/v1/proposals?sort_by=invalid_column');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['sort_by']]);
    }

    public function test_returns_422_for_invalid_sort_direction(): void
    {
        $response = $this->getJson('/api/v1/proposals?sort_direction=invalid');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['sort_direction']]);
    }

    public function test_returns_422_when_per_page_exceeds_maximum(): void
    {
        $response = $this->getJson('/api/v1/proposals?per_page=101');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['per_page']]);
    }
}
