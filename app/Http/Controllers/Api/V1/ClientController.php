<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Client\ShowClientRequest;
use App\Http\Requests\Client\StoreClientRequest;
use Illuminate\Http\JsonResponse;

final class ClientController extends BaseController
{
    public function store(StoreClientRequest $request): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function show(ShowClientRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }
}
