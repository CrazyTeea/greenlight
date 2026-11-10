<?php

namespace App\Http\Actions;

use App\Enums\ApprovalDecision;
use App\Enums\AuditAction;
use App\Enums\RequestStatus;
use App\Events\RequestSubmitted;
use App\Exceptions\Domain\NotRequestOwnerException;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\ApprovalChainResolver;
use App\Services\AuditLogger;
use App\Services\RequestStateMachine;
use Illuminate\Support\Facades\DB;

final readonly class SubmitRequestAction
{
    public function __construct(
        private RequestStateMachine $state,
        private ApprovalChainResolver $chain,
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

            $this->state->assertCan($locked->status, RequestStatus::Pending);

            $approvers = $this->chain->resolve($locked);

            foreach ($approvers as $index => $approver) {
                $locked->approvals()->create([
                    'step' => $index + 1,
                    'approver_id' => $approver->id,
                    'decision' => ApprovalDecision::Pending,
                ]);
            }

            $locked->update([
                'status' => RequestStatus::Pending,
                'submitted_at' => now(),
                'current_approver_id' => $approvers[0]->id,
                'version' => $locked->version + 1,
            ]);

            $this->audit->log($locked, $actor, AuditAction::Submitted, [
                'steps' => count($approvers),
            ]);

            RequestSubmitted::dispatch($locked, $approvers[0]);

            return $locked;
        });
    }
}
