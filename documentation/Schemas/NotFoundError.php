<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotFoundError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Client not found.'),
    ],
    type: 'object',
)]
final class NotFoundError
{
}
