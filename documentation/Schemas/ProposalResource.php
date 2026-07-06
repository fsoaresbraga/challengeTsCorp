<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProposalResource',
    required: ['id', 'client_id', 'product', 'monthly_value', 'status', 'origin', 'version', 'created_at', 'updated_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'client_id', type: 'integer', example: 1),
        new OA\Property(property: 'product', type: 'string', example: 'Cloud Hosting Plan'),
        new OA\Property(property: 'monthly_value', type: 'string', example: '299.90'),
        new OA\Property(property: 'status', type: 'string', enum: ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELED'], example: 'DRAFT'),
        new OA\Property(property: 'origin', type: 'string', enum: ['APP', 'SITE', 'API'], example: 'API'),
        new OA\Property(property: 'version', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
final class ProposalResource
{
}
