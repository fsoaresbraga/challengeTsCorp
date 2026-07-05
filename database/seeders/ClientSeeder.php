<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

final class ClientSeeder extends Seeder
{
    public function run(): void
    {
        Client::factory()->create([
            'name' => 'Acme Corporation',
            'email' => 'contact@acme.test',
            'document' => '12345678901',
        ]);

        Client::factory()->create([
            'name' => 'Globex Industries',
            'email' => 'hello@globex.test',
            'document' => '98765432100019',
        ]);

        Client::factory()->count(3)->create();
    }
}
