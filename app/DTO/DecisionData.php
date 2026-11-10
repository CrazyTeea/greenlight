<?php

namespace App\DTO;

final readonly class DecisionData
{
    public function __construct(
        public bool $approve,
        public ?string $comment,
        public int $version,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromArray(array $validated): self
    {
        return new self(
            approve: (bool) $validated['approve'],
            comment: filled($validated['comment'] ?? null) ? trim((string) $validated['comment']) : null,
            version: (int) $validated['version'],
        );
    }
}
