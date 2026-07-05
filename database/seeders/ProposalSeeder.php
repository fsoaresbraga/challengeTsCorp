<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Proposal;
use Illuminate\Database\Seeder;

final class ProposalSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::query()->limit(2)->get();

        foreach ($clients as $client) {
            Proposal::factory()
                ->for($client)
                ->count(2)
                ->create();

            Proposal::factory()
                ->for($client)
                ->submitted()
                ->create();
        }
    }
}
