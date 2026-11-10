<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

class RequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'amount' => (float) $this->amount,
            'category' => [
                'value' => $this->category->value,
                'label' => $this->category->label(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'final' => $this->status->isFinal(),
            ],
            'requester' => $this->person($this->requester),
            'current_approver' => $this->person($this->currentApprover),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'can' => [
                'submit' => Gate::allows('submit', $this->resource),
                'decide' => Gate::allows('decide', $this->resource),
                'cancel' => Gate::allows('cancel', $this->resource),
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function person(?User $user): ?array
    {
        if ($user === null) {
            return null;
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'initials' => $user->initials(),
            'department' => $user->department,
        ];
    }
}
