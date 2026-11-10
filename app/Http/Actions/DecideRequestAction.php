<?php

namespace App\Http\Actions;

use App\DTO\DecisionData;
use App\Enums\ApprovalDecision;
use App\Enums\AuditAction;
use App\Enums\RequestStatus;
use App\Events\RequestDecided;
use App\Exceptions\Domain\FourEyesException;
use App\Exceptions\Domain\NotYourTurnException;
use App\Exceptions\Domain\StaleRequestException;
use App\Models\Approval;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\RequestStateMachine;
use Illuminate\Support\Facades\DB;

final readonly class DecideRequestAction
{
    public function __construct(
        private RequestStateMachine $state,
        private AuditLogger $audit,
    ) {}

    public function handle(PurchaseRequest $request, User $approver, DecisionData $data): PurchaseRequest
    {
        return DB::transaction(function () use ($request, $approver, $data) {
            /** @var PurchaseRequest $locked */
            $locked = PurchaseRequest::query()->lockForUpdate()->findOrFail($request->id);

            // версия нужна чтобы не принять решение по устаревшей странице
            if ($locked->version !== $data->version) {
                throw new StaleRequestException;
            }

            $this->state->assertCan(
                $locked->status,
                $data->approve ? RequestStatus::Approved : RequestStatus::Rejected,
            );

            if ($locked->requester_id === $approver->id) {
                throw new FourEyesException;
            }

            /** @var Approval|null $current */
            $current = $locked->approvals()
                ->where('decision', ApprovalDecision::Pending)
                ->orderBy('step')
                ->first();

            if ($current === null || $current->approver_id !== $approver->id) {
                throw new NotYourTurnException;
            }

            $current->update([
                'decision' => $data->approve ? ApprovalDecision::Approved : ApprovalDecision::Rejected,
                'comment' => $data->comment,
                'decided_at' => now(),
            ]);

            if (! $data->approve) {
                // после отказа дальше шагов не будет, чистим чтоб не висели
                $locked->approvals()
                    ->where('step', '>', $current->step)
                    ->where('decision', ApprovalDecision::Pending)
                    ->delete();
            }

            $next = $data->approve
                ? $locked->approvals()
                    ->where('step', '>', $current->step)
                    ->where('decision', ApprovalDecision::Pending)
                    ->orderBy('step')
                    ->first()
                : null;

            $finished = ! $data->approve || $next === null;

            $locked->update([
                'status' => match (true) {
                    ! $data->approve => RequestStatus::Rejected,
                    $finished => RequestStatus::Approved,
                    default => RequestStatus::Pending,
                },
                'current_approver_id' => $next?->approver_id,
                'decided_at' => $finished ? now() : null,
                'version' => $locked->version + 1,
            ]);

            $this->audit->log(
                $locked,
                $approver,
                $data->approve ? AuditAction::Approved : AuditAction::Rejected,
                array_filter([
                    'step' => $current->step,
                    'comment' => $data->comment,
                ]),
            );

            RequestDecided::dispatch($locked, $approver, $data->approve);

            return $locked;
        });
    }
}
