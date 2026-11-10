<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Http\Resources\RequestResource;
use App\Models\AuditEvent;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $visible = PurchaseRequest::query()->visibleTo($user);

        $awaiting = (clone $visible)
            ->awaitingDecision($user)
            ->with(['requester', 'currentApprover'])
            ->orderBy('submitted_at')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'awaiting' => (clone $visible)->awaitingDecision($user)->count(),
                'in_review' => (clone $visible)->where('status', RequestStatus::Pending)->count(),
                'approved_this_month' => (float) (clone $visible)
                    ->where('status', RequestStatus::Approved)
                    ->where('decided_at', '>=', now()->startOfMonth())
                    ->sum('amount'),
                'my_drafts' => (clone $visible)
                    ->where('requester_id', $user->id)
                    ->where('status', RequestStatus::Draft)
                    ->count(),
            ],
            'awaiting' => RequestResource::collection($awaiting),
            'recent' => AuditEvent::query()
                ->whereHas('request', fn ($query) => $query->visibleTo($user))
                ->with(['actor', 'request'])
                ->latest('id')
                ->limit(7)
                ->get()
                ->map(fn (AuditEvent $event) => [
                    'id' => $event->id,
                    'action' => [
                        'value' => $event->action->value,
                        'label' => $event->action->label(),
                    ],
                    'comment' => $event->meta['comment'] ?? null,
                    'created_at' => $event->created_at?->toIso8601String(),
                    'actor' => $event->actor?->name,
                    'request' => [
                        'id' => $event->request->id,
                        'code' => $event->request->code,
                        'title' => $event->request->title,
                    ],
                ]),
        ]);
    }
}
