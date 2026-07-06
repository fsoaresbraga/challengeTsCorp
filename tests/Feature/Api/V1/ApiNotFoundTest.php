<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ApiNotFoundTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_json_404_for_unknown_api_route(): void
    {
        $response = $this->getJson('/api/v1/proposalss');

        $response->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('message', 'Route not found.');
    }

    public function test_returns_json_405_for_unsupported_method_on_known_route(): void
    {
        $response = $this->patchJson('/api/v1/clients');

        $response->assertStatus(405)
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('message', 'Method not allowed.');
    }
}
