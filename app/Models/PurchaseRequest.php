<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use Database\Factories\PurchaseRequestFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseRequest extends Model
{
    /** @use HasFactory<PurchaseRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'amount',
        'category',
        'status',
        'requester_id',
        'current_approver_id',
        'submitted_at',
        'decided_at',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'category' => RequestCategory::class,
            'status' => RequestStatus::class,
            'version' => 'integer',
            'submitted_at' => 'datetime',
            'decided_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function currentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class)->orderBy('step');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(AuditEvent::class)->latest('id');
    }

    public function currentApproval(): ?Approval
    {
        return $this->approvals->firstWhere('decision', ApprovalDecision::Pending);
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->role->canSeeAllRequests()
            ? $query
            : $query->where('requester_id', $user->id);
    }

    public function scopeAwaitingDecision(Builder $query, User $user): Builder
    {
        return $query
            ->where('status', RequestStatus::Pending)
            ->where('current_approver_id', $user->id);
    }

    // с этой суммы подключается финдир, см. ApprovalChainResolver
    public const CFO_THRESHOLD = 100000;
}
