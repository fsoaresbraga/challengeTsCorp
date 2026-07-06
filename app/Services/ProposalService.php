<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProposalAuditEvent;
use App\Enums\ProposalOrigin;
use App\Enums\ProposalStatus;
use App\Exceptions\BusinessException;
use App\Exceptions\EntityNotFoundException;
use App\Models\Proposal;
use Illuminate\Support\Facades\DB;

final class ProposalService
{
    public function __construct(
        private readonly ProposalAuditService $auditService,
        private readonly IdempotencyService $idempotencyService,
    ) {}

    /**
     * @param array{client_id: int, product: string, monthly_value: string|float, origin: ProposalOrigin|string} $data
     */
    public function create(array $data, string $idempotencyKey, ?string $actorHeader): Proposal
    {
        $operation = IdempotencyService::OPERATION_PROPOSAL_CREATE;
        $requestHash = $this->idempotencyService->hash($this->payloadForHash($data));

        $existing = $this->idempotencyService->find($operation, $idempotencyKey);

        if ($existing !== null) {
            if ($existing->response_hash !== $requestHash) {
                throw new BusinessException('Idempotency key reused with a different request payload.');
            }

            $proposal = Proposal::query()->find($existing->resource_id);

            if ($proposal === null) {
                throw new EntityNotFoundException('Proposal');
            }

            return $proposal;
        }

        return DB::transaction(function () use ($data, $idempotencyKey, $requestHash, $actorHeader, $operation): Proposal {
            $actor = $this->auditService->resolveActor($actorHeader);
            $origin = $data['origin'] instanceof ProposalOrigin
                ? $data['origin']
                : ProposalOrigin::from((string) $data['origin']);

            $proposal = Proposal::query()->create([
                'client_id' => $data['client_id'],
                'product' => $data['product'],
                'monthly_value' => $data['monthly_value'],
                'status' => ProposalStatus::Draft,
                'origin' => $origin,
                'version' => 1,
            ]);

            $this->auditService->record($proposal, $actor, ProposalAuditEvent::Created, [
                'client_id' => $proposal->client_id,
                'product' => $proposal->product,
                'monthly_value' => (string) $proposal->monthly_value,
                'origin' => $proposal->origin->value,
            ]);

            $this->idempotencyService->register(
                $operation,
                $idempotencyKey,
                $requestHash,
                $proposal->id,
            );

            return $proposal;
        });
    }

    public function findById(int $id): Proposal
    {
        $proposal = Proposal::query()->find($id);

        if ($proposal === null) {
            throw new EntityNotFoundException('Proposal');
        }

        return $proposal;
    }

    /**
     * @param array{client_id: int, product: string, monthly_value: string|float, origin: ProposalOrigin|string} $data
     *
     * @return array<string, int|string>
     */
    private function payloadForHash(array $data): array
    {
        $origin = $data['origin'] instanceof ProposalOrigin
            ? $data['origin']->value
            : (string) $data['origin'];

        return [
            'client_id' => $data['client_id'],
            'product' => $data['product'],
            'monthly_value' => number_format((float) $data['monthly_value'], 2, '.', ''),
            'origin' => $origin,
        ];
    }
}
