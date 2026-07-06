<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ConflictError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Version conflict.'),
    ],
    type: 'object',
)]
final class ConflictError
{
}
