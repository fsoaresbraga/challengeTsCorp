<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Client\ShowClientRequest;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Resources\ClientResource;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;

final class ClientController extends BaseController
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clientService->create($request->validated());

        return (new ClientResource($client))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ShowClientRequest $request, int $id): JsonResponse
    {
        $client = $this->clientService->findById($id);

        return (new ClientResource($client))->response();
    }
}
