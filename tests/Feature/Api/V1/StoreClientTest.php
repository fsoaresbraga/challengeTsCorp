<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class StoreClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_client_with_valid_cpf(): void
    {
        $response = $this->postJson('/api/v1/clients', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'document' => '529.982.247-25',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'document', 'created_at', 'updated_at'],
            ])
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com')
            ->assertJsonPath('data.document', '52998224725');

        $this->assertDatabaseHas('clients', [
            'email' => 'john@example.com',
            'document' => '52998224725',
        ]);
    }

    public function test_creates_client_with_valid_alphanumeric_cnpj(): void
    {
        $response = $this->postJson('/api/v1/clients', [
            'name' => 'Acme Corp',
            'email' => 'contact@acme.example',
            'document' => '12.ABC.345/01DE-35',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.document', '12ABC34501DE35');

        $this->assertDatabaseHas('clients', [
            'document' => '12ABC34501DE35',
        ]);
    }

    public function test_returns_422_when_required_fields_are_missing(): void
    {
        $response = $this->postJson('/api/v1/clients', []);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonStructure(['errors' => ['name', 'email', 'document']]);
    }

    public function test_returns_422_when_email_is_duplicate(): void
    {
        Client::factory()->create([
            'email' => 'taken@example.com',
            'document' => '52998224725',
        ]);

        $response = $this->postJson('/api/v1/clients', [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'document' => '12ABC34501DE35',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'The email has already been taken.');
    }

    public function test_returns_422_when_document_is_duplicate(): void
    {
        Client::factory()->create([
            'email' => 'existing@example.com',
            'document' => '52998224725',
        ]);

        $response = $this->postJson('/api/v1/clients', [
            'name' => 'Another User',
            'email' => 'new@example.com',
            'document' => '52998224725',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.document.0', 'The document has already been taken.');
    }

    public function test_returns_422_when_document_is_invalid(): void
    {
        $response = $this->postJson('/api/v1/clients', [
            'name' => 'Invalid Doc',
            'email' => 'invalid@example.com',
            'document' => '00000000000',
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.document.0', 'The document must be a valid CPF or CNPJ.');
    }
}
