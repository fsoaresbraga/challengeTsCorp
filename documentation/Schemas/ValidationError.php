<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
        new OA\Property(property: 'errors', type: 'object'),
    ],
    type: 'object',
)]
final class ValidationError
{
}
