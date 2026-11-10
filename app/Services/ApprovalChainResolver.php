<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\PurchaseRequest;
use App\Models\User;

final class ApprovalChainResolver
{
    // руководитель, а от 100к ещё и финдир.
    // автора выкидываем из цепочки, иначе он упрётся в four eyes и заявка зависнет

    /**
     * @return list<User>
     */
    public function resolve(PurchaseRequest $request): array
    {
        $roles = [Role::Manager];

        if ($request->amount >= PurchaseRequest::CFO_THRESHOLD) {
            $roles[] = Role::Cfo;
        }

        $approvers = collect($roles)
            ->map(fn (Role $role) => User::query()->where('role', $role)->orderBy('id')->firstOrFail())
            ->reject(fn (User $user) => $user->id === $request->requester_id)
            ->values();

        // автор сам руководитель, значит сразу на финдира
        if ($approvers->isEmpty()) {
            $approvers->push(User::query()->where('role', Role::Cfo)->orderBy('id')->firstOrFail());
        }

        return $approvers->all();
    }
}
