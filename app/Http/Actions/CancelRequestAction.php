<?php

namespace App\Http\Actions;

use App\Enums\ApprovalDecision;
use App\Enums\AuditAction;
use App\Enums\RequestStatus;
use App\Exceptions\Domain\NotRequestOwnerException;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestStateMachine;
use Illuminate\Support\Facades\DB;

final readonly class CancelRequestAction
{
    public function __construct(
        private RequestStateMachine $state,
        private AuditLogger $audit,
    ) {}

    public function handle(PurchaseRequest $request, User $actor): PurchaseRequest
    {
        return DB::transaction(function () use ($request, $actor) {
            /** @var PurchaseRequest $locked */
            $locked = PurchaseRequest::query()->lockForUpdate()->findOrFail($request->id);

            if ($locked->requester_id !== $actor->id) {
                throw new NotRequestOwnerException;
            }

            $this->state->assertCan($locked->status, RequestStatus::Cancelled);

            $locked->approvals()
                ->where('decision', ApprovalDecision::Pending)
                ->delete();

            $locked->update([
                'status' => RequestStatus::Cancelled,
                'current_approver_id' => null,
                'decided_at' => now(),
                'version' => $locked->version + 1,
            ]);

            $this->audit->log($locked, $actor, AuditAction::Cancelled);

            return $locked;
        });
    }
}
