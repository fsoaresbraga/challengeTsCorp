<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BusinessError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Idempotency key reused with a different request payload.'),
    ],
    type: 'object',
)]
final class BusinessError
{
}
