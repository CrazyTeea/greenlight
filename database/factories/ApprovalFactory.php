<?php

namespace Database\Factories;

use App\Enums\ApprovalDecision;
use App\Models\Approval;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Approval>
 */
class ApprovalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_request_id' => PurchaseRequest::factory(),
            'step' => 1,
            'approver_id' => User::factory()->manager(),
            'decision' => ApprovalDecision::Pending,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'decision' => ApprovalDecision::Approved,
            'decided_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'decision' => ApprovalDecision::Rejected,
            'decided_at' => now(),
        ]);
    }
}
