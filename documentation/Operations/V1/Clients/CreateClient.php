<?php

declare(strict_types=1);

namespace Documentation\Operations\V1\Clients;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/clients',
    operationId: 'createClient',
    description: 'Register a new client (RF-01).',
    tags: ['Clients'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'document'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Acme Corp'),
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'contact@acme.example'),
                new OA\Property(
                    property: 'document',
                    type: 'string',
                    description: 'CPF (11 digits) or alphanumeric CNPJ (RFB IN 2.229/2024)',
                    example: '12ABC34501DE35',
                ),
            ],
        ),
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Client created',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ClientResource'),
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
final class CreateClient
{
}
