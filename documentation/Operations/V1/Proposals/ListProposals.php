<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/proposals',
    operationId: 'listProposals',
    description: 'Search proposals with filters, sorting, and pagination (RF-11, RF-12).',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'CANCELED'],
            ),
        ),
        new OA\Parameter(
            name: 'client_id',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
        ),
        new OA\Parameter(
            name: 'product',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', maxLength: 255, example: 'Cloud'),
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
        new OA\Parameter(
            name: 'sort_by',
            in: 'query',
            required: false,
            schema: new OA\Schema(
                type: 'string',
                enum: ['created_at', 'monthly_value', 'product', 'status'],
                example: 'created_at',
            ),
        ),
        new OA\Parameter(
            name: 'sort_direction',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], example: 'desc'),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated proposal list',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/ProposalResource'),
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
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
final class ListProposals
{
}
