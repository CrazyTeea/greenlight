<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class RequestDetailResource extends RequestResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->description,
            'version' => $this->version,
            'decided_at' => $this->decided_at?->toIso8601String(),
            'approvals' => $this->approvals->map(fn ($approval) => [
                'id' => $approval->id,
                'step' => $approval->step,
                'decision' => [
                    'value' => $approval->decision->value,
                    'label' => $approval->decision->label(),
                ],
                'comment' => $approval->comment,
                'decided_at' => $approval->decided_at?->toIso8601String(),
                'approver' => $this->person($approval->approver),
            ]),
            'audit' => $this->auditEvents->map(fn ($event) => [
                'id' => $event->id,
                'action' => [
                    'value' => $event->action->value,
                    'label' => $event->action->label(),
                ],
                'comment' => $event->meta['comment'] ?? null,
                'created_at' => $event->created_at?->toIso8601String(),
                'actor' => $this->person($event->actor),
            ]),
        ];
    }
}
