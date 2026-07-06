<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Proposals;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/proposals/{id}/cancel',
    operationId: 'cancelProposal',
    description: 'Cancel a DRAFT or SUBMITTED proposal with optimistic lock.',
    tags: ['Proposals'],
    parameters: [
        new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)),
        new OA\Parameter(name: 'X-Actor', in: 'header', required: false, schema: new OA\Schema(type: 'string', example: 'user:42')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['version'],
            properties: [new OA\Property(property: 'version', type: 'integer', minimum: 1, example: 1)],
        ),
    ),
    responses: [
        new OA\Response(response: 200, description: 'Proposal canceled'),
        new OA\Response(response: 404, description: 'Proposal not found'),
        new OA\Response(response: 409, description: 'Optimistic lock conflict', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError')),
        new OA\Response(response: 422, description: 'Validation or transition error'),
    ],
)]
final class CancelProposal
{
}
