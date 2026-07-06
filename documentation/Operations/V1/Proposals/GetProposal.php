<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/proposals/{id}',
    operationId: 'getProposal',
    description: 'Retrieve a proposal by ID (RF-03).',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer', minimum: 1, example: 1),
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Proposal found',
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
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
final class GetProposal
{
}
