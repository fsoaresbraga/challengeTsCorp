<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Patch(
    path: '/api/v1/proposals/{id}',
    operationId: 'updateProposal',
    description: 'Update proposal fields in DRAFT status with optimistic lock (RF-04).',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
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
            required: ['version'],
            properties: [
                new OA\Property(property: 'version', type: 'integer', minimum: 1, example: 1),
                new OA\Property(property: 'product', type: 'string', example: 'Cloud Hosting Premium'),
                new OA\Property(property: 'monthly_value', type: 'number', format: 'float', minimum: 0.01, example: 399.90),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Proposal updated',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProposalResource'),
                ],
            ),
        ),
        new OA\Response(
            response: 404,
            description: 'Proposal not found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Proposal not found.'),
                ],
            ),
        ),
        new OA\Response(
            response: 409,
            description: 'Optimistic lock conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'),
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
final class UpdateProposal
{
}
