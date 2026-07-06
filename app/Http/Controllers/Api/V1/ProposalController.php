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
use App\Http\Resources\ProposalResource;
use App\Services\ProposalSearchService;
use App\Services\ProposalService;
use Illuminate\Http\JsonResponse;

final class ProposalController extends BaseController
{
    public function __construct(
        private readonly ProposalService $proposalService,
        private readonly ProposalSearchService $proposalSearchService,
    ) {}

    public function index(ListProposalsRequest $request): JsonResponse
    {
        $proposals = $this->proposalSearchService->search($request->validated());

        return ProposalResource::collection($proposals)->response();
    }

    public function store(StoreProposalRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $idempotencyKey = (string) $validated['idempotency_key'];
        unset($validated['idempotency_key']);

        $proposal = $this->proposalService->create(
            $validated,
            $idempotencyKey,
            $request->header('X-Actor'),
        );

        return (new ProposalResource($proposal))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ShowProposalRequest $request, int $id): JsonResponse
    {
        $proposal = $this->proposalService->findById($id);

        return (new ProposalResource($proposal))->response();
    }

    public function update(UpdateProposalRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $version = (int) $validated['version'];
        unset($validated['version'], $validated['id']);

        $proposal = $this->proposalService->update(
            $id,
            $version,
            $validated,
            $request->header('X-Actor'),
        );

        return (new ProposalResource($proposal))->response();
    }

    public function destroy(DestroyProposalRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $this->proposalService->destroy($id, (int) $validated['version'], $request->header('X-Actor'));

        return response()->json(null, 204);
    }

    public function submit(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $proposal = $this->proposalService->submit(
            $id,
            (int) $validated['version'],
            (string) $validated['idempotency_key'],
            $request->header('X-Actor'),
        );

        return (new ProposalResource($proposal))->response();
    }

    public function approve(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $proposal = $this->proposalService->approve($id, (int) $validated['version'], $request->header('X-Actor'));

        return (new ProposalResource($proposal))->response();
    }

    public function reject(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $proposal = $this->proposalService->reject($id, (int) $validated['version'], $request->header('X-Actor'));

        return (new ProposalResource($proposal))->response();
    }

    public function cancel(ProposalStatusActionRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $proposal = $this->proposalService->cancel($id, (int) $validated['version'], $request->header('X-Actor'));

        return (new ProposalResource($proposal))->response();
    }

    public function audit(ListProposalAuditsRequest $request, int $id): JsonResponse
    {
        abort(501, 'Not implemented');
    }
}
