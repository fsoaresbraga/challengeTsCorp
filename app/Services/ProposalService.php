<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProposalAuditEvent;
use App\Enums\ProposalOrigin;
use App\Enums\ProposalStatus;
use App\Exceptions\BusinessException;
use App\Exceptions\EntityNotFoundException;
use App\Exceptions\OptimisticLockException;
use App\Models\Proposal;
use Illuminate\Support\Facades\DB;

final class ProposalService
{
    public function __construct(
        private readonly ProposalAuditService $auditService,
        private readonly IdempotencyService $idempotencyService,
        private readonly ProposalStatusService $statusService,
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

            $this->idempotencyService->register($operation, $idempotencyKey, $requestHash, $proposal->id);

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
     * @param array{product?: string, monthly_value?: string|float} $data
     */
    public function update(int $id, int $version, array $data, ?string $actorHeader): Proposal
    {
        $proposal = $this->findById($id);
        if ($proposal->status !== ProposalStatus::Draft) {
            throw new BusinessException('Proposal cannot be updated in current status.');
        }

        $this->statusService->assertVersion($proposal, $version);
        $actor = $this->auditService->resolveActor($actorHeader);
        $auditPayload = [];

        if (array_key_exists('product', $data) && $data['product'] !== $proposal->product) {
            $auditPayload['product'] = ['from' => $proposal->product, 'to' => $data['product']];
            $proposal->product = $data['product'];
        }

        if (array_key_exists('monthly_value', $data)) {
            $newValue = number_format((float) $data['monthly_value'], 2, '.', '');
            $currentValue = number_format((float) $proposal->monthly_value, 2, '.', '');
            if ($newValue !== $currentValue) {
                $auditPayload['monthly_value'] = ['from' => $currentValue, 'to' => $newValue];
                $proposal->monthly_value = $newValue;
            }
        }

        return DB::transaction(function () use ($proposal, $version, $actor, $auditPayload): Proposal {
            $affected = Proposal::query()
                ->whereKey($proposal->id)
                ->where('version', $version)
                ->update([
                    'product' => $proposal->product,
                    'monthly_value' => $proposal->monthly_value,
                    'version' => $version + 1,
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                throw new OptimisticLockException();
            }

            $proposal->refresh();
            if ($auditPayload !== []) {
                $this->auditService->record($proposal, $actor, ProposalAuditEvent::UpdatedFields, $auditPayload);
            }

            return $proposal;
        });
    }

    public function destroy(int $id, int $version, ?string $actorHeader): void
    {
        $proposal = $this->findById($id);
        if (! in_array($proposal->status, [ProposalStatus::Draft, ProposalStatus::Submitted], true)) {
            throw new BusinessException('Proposal cannot be deleted in current status.');
        }

        $this->statusService->assertVersion($proposal, $version);
        $actor = $this->auditService->resolveActor($actorHeader);

        DB::transaction(function () use ($proposal, $version, $actor): void {
            $affected = Proposal::query()
                ->whereKey($proposal->id)
                ->where('version', $version)
                ->update([
                    'version' => $version + 1,
                    'updated_at' => now(),
                ]);
            if ($affected === 0) {
                throw new OptimisticLockException();
            }

            $proposal->refresh();
            $this->auditService->record($proposal, $actor, ProposalAuditEvent::DeletedLogical, [
                'status' => $proposal->status->value,
            ]);
            $proposal->delete();
        });
    }

    public function submit(int $id, int $version, string $idempotencyKey, ?string $actorHeader): Proposal
    {
        $operation = IdempotencyService::OPERATION_PROPOSAL_SUBMIT;
        $requestHash = $this->idempotencyService->hash([
            'proposal_id' => $id,
            'version' => $version,
            'to_status' => ProposalStatus::Submitted->value,
        ]);

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

        $proposal = $this->changeStatus($id, $version, ProposalStatus::Submitted, $actorHeader);
        $this->idempotencyService->register($operation, $idempotencyKey, $requestHash, $proposal->id);

        return $proposal;
    }

    public function approve(int $id, int $version, ?string $actorHeader): Proposal
    {
        return $this->changeStatus($id, $version, ProposalStatus::Approved, $actorHeader);
    }

    public function reject(int $id, int $version, ?string $actorHeader): Proposal
    {
        return $this->changeStatus($id, $version, ProposalStatus::Rejected, $actorHeader);
    }

    public function cancel(int $id, int $version, ?string $actorHeader): Proposal
    {
        return $this->changeStatus($id, $version, ProposalStatus::Canceled, $actorHeader);
    }

    private function changeStatus(int $id, int $version, ProposalStatus $toStatus, ?string $actorHeader): Proposal
    {
        $proposal = $this->findById($id);
        $this->statusService->assertVersion($proposal, $version);
        $this->statusService->assertTransition($proposal->status, $toStatus);
        $actor = $this->auditService->resolveActor($actorHeader);
        $fromStatus = $proposal->status;

        return DB::transaction(function () use ($proposal, $version, $toStatus, $actor, $fromStatus): Proposal {
            $affected = Proposal::query()
                ->whereKey($proposal->id)
                ->where('version', $version)
                ->update([
                    'status' => $toStatus->value,
                    'version' => $version + 1,
                    'updated_at' => now(),
                ]);
            if ($affected === 0) {
                throw new OptimisticLockException();
            }

            $proposal->refresh();
            $this->auditService->record($proposal, $actor, ProposalAuditEvent::StatusChanged, [
                'from' => $fromStatus->value,
                'to' => $toStatus->value,
            ]);

            return $proposal;
        });
    }

    /**
     * @param array{client_id: int, product: string, monthly_value: string|float, origin: ProposalOrigin|string} $data
     * @return array<string, int|string>
     */
    private function payloadForHash(array $data): array
    {
        $origin = $data['origin'] instanceof ProposalOrigin ? $data['origin']->value : (string) $data['origin'];

        return [
            'client_id' => $data['client_id'],
            'product' => $data['product'],
            'monthly_value' => number_format((float) $data['monthly_value'], 2, '.', ''),
            'origin' => $origin,
        ];
    }
}
