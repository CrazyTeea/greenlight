<?php

namespace App\Events;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly PurchaseRequest $request,
        public readonly User $actor,
        public readonly bool $approved,
    ) {}
}
