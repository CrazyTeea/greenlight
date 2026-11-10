<?php

namespace App\DTO;

use App\Enums\RequestCategory;

final readonly class CreateRequestData
{
    public function __construct(
        public string $title,
        public string $description,
        public float $amount,
        public RequestCategory $category,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            title: trim((string) $validated['title']),
            description: trim((string) ($validated['description'] ?? '')),
            amount: (float) $validated['amount'],
            category: RequestCategory::from((string) $validated['category']),
        );
    }
}
