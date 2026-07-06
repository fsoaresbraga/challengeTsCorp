<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IdempotencyKey;

final class IdempotencyService
{
    public const OPERATION_PROPOSAL_CREATE = 'proposal.create';

    public const OPERATION_PROPOSAL_SUBMIT = 'proposal.submit';

    public function find(string $operation, string $idempotencyKey): ?IdempotencyKey
    {
        return IdempotencyKey::query()
            ->where('operation', $operation)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    public function register(
        string $operation,
        string $idempotencyKey,
        string $responseHash,
        int $resourceId,
    ): IdempotencyKey {
        return IdempotencyKey::query()->create([
            'operation' => $operation,
            'idempotency_key' => $idempotencyKey,
            'response_hash' => $responseHash,
            'resource_id' => $resourceId,
        ]);
    }

    /** @param array<string, mixed> $payload */
    public function hash(array $payload): string
    {
        ksort($payload);

        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
