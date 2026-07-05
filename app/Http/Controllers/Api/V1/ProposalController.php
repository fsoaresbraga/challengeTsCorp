<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Proposal\DestroyProposalRequest;
use App\Http\Requests\Proposal\ListProposalAuditsRequest;
use App\Http\Requests\Proposal\ListProposalsRequest;
use App\Http\Requests\Proposal\ProposalStatusActionRequest;
use App\Http\Requests\Proposal\ShowProposalRequest;
use App\Http\Requests\Proposal\StoreProposalRequest;
use App\Http\Requests\Proposal\UpdateProposalRequest;
use Illuminate\Http\JsonResponse;

final class ProposalController extends BaseController
{
    public function index(ListProposalsRequest $request): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function store(StoreProposalRequest $request): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function show(ShowProposalRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function update(UpdateProposalRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function destroy(DestroyProposalRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function submit(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function approve(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function reject(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function cancel(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }

    public function audit(ListProposalAuditsRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }
}
