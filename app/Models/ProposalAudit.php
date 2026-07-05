<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProposalAuditEvent;
use Database\Factories\ProposalAuditFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProposalAudit extends Model
{
    /** @use HasFactory<ProposalAuditFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'proposal_id',
        'actor',
        'event',
        'payload',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    protected function casts(): array
    {
        return [
            'event' => ProposalAuditEvent::class,
            'payload' => 'array',
        ];
    }
}
