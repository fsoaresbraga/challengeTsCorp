<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/proposals/{id}/reject',
    operationId: 'rejectProposal',
    description: 'Reject a SUBMITTED proposal with optimistic lock (RF-07).',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
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
            properties: [new OA\Property(property: 'version', type: 'integer', minimum: 1, example: 2)],
        ),
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Proposal rejected',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ProposalResource'),
                ],
            ),
        ),
        new OA\Response(
            response: 404,
            description: 'Proposal not found',
            content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'),
        ),
        new OA\Response(
            response: 409,
            description: 'Optimistic lock conflict',
            content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation, business rule, or transition error',
            content: new OA\JsonContent(
                oneOf: [
                    new OA\Schema(ref: '#/components/schemas/ValidationError'),
                    new OA\Schema(ref: '#/components/schemas/BusinessError'),
                ],
            ),
        ),
    ],
)]
final class RejectProposal
{
}
