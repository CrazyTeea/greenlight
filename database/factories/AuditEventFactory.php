<?php

namespace Database\Factories;

use App\Enums\AuditAction;
use App\Models\AuditEvent;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditEvent>
 */
class AuditEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_request_id' => PurchaseRequest::factory(),
            'actor_id' => User::factory(),
            'action' => AuditAction::Created,
            'meta' => null,
            'created_at' => now(),
        ];
    }
}
