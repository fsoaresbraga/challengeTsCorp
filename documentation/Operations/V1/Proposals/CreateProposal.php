<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/proposals',
    operationId: 'createProposal',
    description: 'Create a new proposal in DRAFT status (RF-03). Requires Idempotency-Key header.',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(
            name: 'Idempotency-Key',
            in: 'header',
            required: true,
            schema: new OA\Schema(type: 'string', example: 'create-proposal-abc-123'),
        ),
        new OA\Parameter(
            name: 'X-Actor',
            in: 'header',
            required: false,
            description: 'Actor identifier: `system` or `user:{id}` (defaults to system)',
            schema: new OA\Schema(type: 'string', example: 'user:42'),
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['client_id', 'product', 'monthly_value', 'origin'],
            properties: [
                new OA\Property(property: 'client_id', type: 'integer', example: 1),
                new OA\Property(property: 'product', type: 'string', example: 'Cloud Hosting Plan'),
                new OA\Property(property: 'monthly_value', type: 'number', format: 'float', minimum: 0.01, example: 299.90),
                new OA\Property(property: 'origin', type: 'string', enum: ['APP', 'SITE', 'API'], example: 'API'),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Proposal created',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProposalResource'),
                ],
            ),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation or business rule error',
            content: new OA\JsonContent(
                oneOf: [
                    new OA\Schema(ref: '#/components/schemas/ValidationError'),
                    new OA\Schema(ref: '#/components/schemas/BusinessError'),
                ],
            ),
        ),
    ],
)]
final class CreateProposal
{
}
