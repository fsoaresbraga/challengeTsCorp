<?php

declare(strict_types=1);

namespace Documentation\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProposalAuditResource',
    required: ['id', 'proposal_id', 'actor', 'event', 'payload', 'created_at'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'proposal_id', type: 'integer', example: 1),
        new OA\Property(property: 'actor', type: 'string', example: 'system'),
        new OA\Property(
            property: 'event',
            type: 'string',
            enum: ['CREATED', 'UPDATED_FIELDS', 'STATUS_CHANGED', 'DELETED_LOGICAL'],
            example: 'CREATED',
        ),
        new OA\Property(property: 'payload', type: 'object', example: ['client_id' => 1, 'product' => 'Cloud Hosting Plan']),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
    type: 'object',
)]
final class ProposalAuditResource
{
}
