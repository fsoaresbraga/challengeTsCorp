<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/proposals/{id}/audit',
    operationId: 'listProposalAudits',
    description: 'List audit trail entries for a proposal (RF-10).',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
        ),
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
        ),
        new OA\Parameter(
            name: 'per_page',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 15),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated audit trail',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/ProposalAuditResource'),
                    ),
                    new OA\Property(
                        property: 'links',
                        properties: [
                            new OA\Property(property: 'first', type: 'string', format: 'uri'),
                            new OA\Property(property: 'last', type: 'string', format: 'uri'),
                            new OA\Property(property: 'prev', type: 'string', format: 'uri', nullable: true),
                            new OA\Property(property: 'next', type: 'string', format: 'uri', nullable: true),
                        ],
                        type: 'object',
                    ),
                    new OA\Property(
                        property: 'meta',
                        properties: [
                            new OA\Property(property: 'current_page', type: 'integer', example: 1),
                            new OA\Property(property: 'from', type: 'integer', example: 1, nullable: true),
                            new OA\Property(property: 'last_page', type: 'integer', example: 1),
                            new OA\Property(property: 'per_page', type: 'integer', example: 15),
                            new OA\Property(property: 'to', type: 'integer', example: 15, nullable: true),
                            new OA\Property(property: 'total', type: 'integer', example: 1),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        ),
        new OA\Response(
            response: 404,
            description: 'Proposal not found',
            content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
final class ListProposalAudits
{
}
