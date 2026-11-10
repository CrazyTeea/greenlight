<?php

namespace App\Policies;

use App\Enums\RequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestPolicy
{
    public function view(User $user, PurchaseRequest $request): bool
    {
        return $user->role->canSeeAllRequests() || $request->requester_id === $user->id;
    }

    public function submit(User $user, PurchaseRequest $request): bool
    {
        return $request->requester_id === $user->id
            && $request->status === RequestStatus::Draft;
    }

    public function decide(User $user, PurchaseRequest $request): bool
    {
        return $request->status === RequestStatus::Pending
            && $request->current_approver_id === $user->id
            && $request->requester_id !== $user->id;
    }

    public function cancel(User $user, PurchaseRequest $request): bool
    {
        return $request->requester_id === $user->id
            && in_array($request->status, [RequestStatus::Draft, RequestStatus::Pending], true);
    }
}
