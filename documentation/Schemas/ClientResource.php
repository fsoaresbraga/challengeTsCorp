<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ClientResource',
    required: ['id', 'name', 'email', 'document', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Acme Corp'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'contact@acme.example'),
        new OA\Property(property: 'document', type: 'string', example: '12ABC34501DE35'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
final class ClientResource
{
}
