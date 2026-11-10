<?php

namespace Database\Factories;

use App\Enums\RequestCategory;
use App\Enums\RequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'REQ-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'amount' => fake()->numberBetween(5000, 90000),
            'category' => fake()->randomElement(RequestCategory::cases()),
            'status' => RequestStatus::Draft,
            'requester_id' => User::factory(),
            'version' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => RequestStatus::Pending,
            'submitted_at' => now()->subDay(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => RequestStatus::Approved,
            'submitted_at' => now()->subDays(3),
            'decided_at' => now()->subDays(2),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => RequestStatus::Rejected,
            'submitted_at' => now()->subDays(3),
            'decided_at' => now()->subDays(2),
        ]);
    }
}
