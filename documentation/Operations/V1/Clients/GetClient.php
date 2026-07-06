<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Clients;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/clients/{id}',
    operationId: 'getClient',
    description: 'Retrieve a client by ID (RF-02).',
    tags: ['Clients'],
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
            description: 'Client found',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ClientResource'),
                ],
            ),
        ),
        new OA\Response(
            response: 404,
            description: 'Client not found',
            content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'),
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error',
            content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'),
        ),
    ],
)]
final class GetClient
{
}
