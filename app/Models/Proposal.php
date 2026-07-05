<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProposalOrigin;
use App\Enums\ProposalStatus;
use Database\Factories\ProposalFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Proposal extends Model
{
    /** @use HasFactory<ProposalFactory> */
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'product',
        'monthly_value',
        'status',
        'origin',
        'version',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ProposalAudit::class);
    }

    public function scopeByStatus(Builder $query, ProposalStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByProduct(Builder $query, string $product): Builder
    {
        return $query->where('product', 'like', '%' . $product . '%');
    }

    protected function casts(): array
    {
        return [
            'status' => ProposalStatus::class,
            'origin' => ProposalOrigin::class,
            'monthly_value' => 'decimal:2',
            'version' => 'integer',
        ];
    }
}
