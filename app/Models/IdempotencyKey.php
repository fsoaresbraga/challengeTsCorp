<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class IdempotencyKey extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'idempotency_keys';

    protected $fillable = [
        'operation',
        'idempotency_key',
        'response_hash',
        'resource_id',
    ];

    protected function casts(): array
    {
        return [
            'resource_id' => 'integer',
        ];
    }
}
