<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Client\ShowClientRequest;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Clients', description: 'Client management')]
final class ClientController extends BaseController
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

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
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Acme Corp'),
                                new OA\Property(property: 'email', type: 'string', format: 'email'),
                                new OA\Property(property: 'document', type: 'string'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Validation failed'),
                        new OA\Property(property: 'errors', type: 'object'),
                    ],
                ),
            ),
        ],
    )]
    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ShowClientRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }
}
