<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\ProposalAuditEvent;
use App\Enums\ProposalOrigin;
use App\Enums\ProposalStatus;
use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreProposalTest extends TestCase
{
    use RefreshDatabase;

    private const IDEMPOTENCY_HEADER = 'Idempotency-Key';

    public function test_creates_proposal_with_valid_payload(): void
    {
        $client = Client::factory()->create();

        $response = $this->postJson('/api/v1/proposals', [
            'client_id' => $client->id,
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ], [
            self::IDEMPOTENCY_HEADER => 'create-proposal-001',
            'X-Actor' => 'user:42',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'client_id', 'product', 'monthly_value', 'status', 'origin', 'version', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.client_id', $client->id)
            ->assertJsonPath('data.product', 'Cloud Hosting Plan')
            ->assertJsonPath('data.monthly_value', '299.90')
            ->assertJsonPath('data.status', ProposalStatus::Draft->value)
            ->assertJsonPath('data.origin', ProposalOrigin::Api->value)
            ->assertJsonPath('data.version', 1);

        $this->assertDatabaseHas('proposals', [
            'client_id' => $client->id,
            'product' => 'Cloud Hosting Plan',
            'status' => ProposalStatus::Draft->value,
        ]);

        $this->assertDatabaseHas('proposal_audits', [
            'proposal_id' => $response->json('data.id'),
            'actor' => 'user:42',
            'event' => ProposalAuditEvent::Created->value,
        ]);
    }

    public function test_returns_422_when_idempotency_key_header_is_missing(): void
    {
        $client = Client::factory()->create();

        $response = $this->postJson('/api/v1/proposals', [
            'client_id' => $client->id,
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.idempotency_key.0', 'The Idempotency-Key header is required.');
    }

    public function test_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/v1/proposals', [], [
            self::IDEMPOTENCY_HEADER => 'create-proposal-002',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['client_id', 'product', 'monthly_value', 'origin']]);
    }

    public function test_returns_422_when_client_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/proposals', [
            'client_id' => 999,
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ], [
            self::IDEMPOTENCY_HEADER => 'create-proposal-003',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.client_id.0', 'The selected client does not exist.');
    }

    public function test_replays_same_response_for_duplicate_idempotency_key(): void
    {
        $client = Client::factory()->create();
        $payload = [
            'client_id' => $client->id,
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ];
        $headers = [self::IDEMPOTENCY_HEADER => 'create-proposal-004'];

        $first = $this->postJson('/api/v1/proposals', $payload, $headers);
        $second = $this->postJson('/api/v1/proposals', $payload, $headers);

        $first->assertCreated();
        $second->assertCreated()
            ->assertJsonPath('data.id', $first->json('data.id'));

        $this->assertSame(1, Proposal::query()->count());
        $this->assertDatabaseCount('idempotency_keys', 1);
    }

    public function test_returns_422_when_idempotency_key_is_reused_with_different_payload(): void
    {
        $client = Client::factory()->create();
        $headers = [self::IDEMPOTENCY_HEADER => 'create-proposal-005'];

        $this->postJson('/api/v1/proposals', [
            'client_id' => $client->id,
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ], $headers)->assertCreated();

        $response = $this->postJson('/api/v1/proposals', [
            'client_id' => $client->id,
            'product' => 'Different Product',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ], $headers);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Idempotency key reused with a different request payload.');

        $this->assertSame(1, Proposal::query()->count());
    }

    public function test_returns_422_when_actor_header_is_invalid(): void
    {
        $client = Client::factory()->create();

        $response = $this->postJson('/api/v1/proposals', [
            'client_id' => $client->id,
            'product' => 'Cloud Hosting Plan',
            'monthly_value' => 299.90,
            'origin' => ProposalOrigin::Api->value,
        ], [
            self::IDEMPOTENCY_HEADER => 'create-proposal-006',
            'X-Actor' => 'invalid-actor',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'The actor must be "system" or "user:{id}".');
    }
}
