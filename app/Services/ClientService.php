<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;

final class ClientService
{
    public function create(array $data): Client
    {
        return Client::query()->create($data);
    }
}
