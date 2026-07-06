<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\EntityNotFoundException;
use App\Models\Client;

final class ClientService
{
    public function create(array $data): Client
    {
        return Client::query()->create($data);
    }

    public function findById(int $id): Client
    {
        $client = Client::query()->find($id);

        if ($client === null) {
            throw new EntityNotFoundException('Client');
        }

        return $client;
    }
}
