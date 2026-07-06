<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ShowClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_client_by_id(): void
    {
        $client = Client::factory()->create([
            'name' => 'Acme Corp',
            'email' => 'contact@acme.example',
            'document' => '52998224725',
        ]);

        $response = $this->getJson("/api/v1/clients/{$client->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'document', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.id', $client->id)
            ->assertJsonPath('data.name', 'Acme Corp')
            ->assertJsonPath('data.email', 'contact@acme.example')
            ->assertJsonPath('data.document', '52998224725');
    }

    public function test_returns_404_when_client_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/clients/999');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Client not found.');
    }

    public function test_returns_422_when_id_is_invalid(): void
    {
        $response = $this->getJson('/api/v1/clients/0');

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['id']]);
    }
}
