<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\PurchaseRequest;
use App\Models\User;

final class AuditLogger
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function log(PurchaseRequest $request, ?User $actor, AuditAction $action, array $meta = []): AuditEvent
    {
        return $request->auditEvents()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'meta' => $meta ?: null,
            'created_at' => now(),
        ]);
    }
}
