<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ProposalSearchService
{
    private const DEFAULT_PER_PAGE = 15;

    private const DEFAULT_SORT_BY = 'created_at';

    private const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * @param array{
     *     status?: ProposalStatus|string,
     *     client_id?: int,
     *     product?: string,
     *     page?: int,
     *     per_page?: int,
     *     sort_by?: string,
     *     sort_direction?: string
     * } $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Proposal::query();

        if (isset($filters['status'])) {
            $status = $filters['status'] instanceof ProposalStatus
                ? $filters['status']
                : ProposalStatus::from((string) $filters['status']);
            $query->byStatus($status);
        }

        if (isset($filters['client_id'])) {
            $query->forClient((int) $filters['client_id']);
        }

        if (isset($filters['product'])) {
            $query->byProduct((string) $filters['product']);
        }

        $sortBy = $filters['sort_by'] ?? self::DEFAULT_SORT_BY;
        $sortDirection = $filters['sort_direction'] ?? self::DEFAULT_SORT_DIRECTION;

        return $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate(
                perPage: $filters['per_page'] ?? self::DEFAULT_PER_PAGE,
                page: $filters['page'] ?? null,
            );
    }
}
