<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use Database\Factories\ApprovalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Approval extends Model
{
    /** @use HasFactory<ApprovalFactory> */
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'step',
        'approver_id',
        'decision',
        'comment',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'step' => 'integer',
            'decision' => ApprovalDecision::class,
            'decided_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
