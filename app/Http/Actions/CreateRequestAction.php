<?php

namespace App\Http\Actions;

use App\DTO\CreateRequestData;
use App\Enums\AuditAction;
use App\Enums\RequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

final readonly class CreateRequestAction
{
    public function __construct(private AuditLogger $audit) {}

    public function handle(User $author, CreateRequestData $data): PurchaseRequest
    {
        return DB::transaction(function () use ($author, $data) {
            $request = PurchaseRequest::create([
                'title' => $data->title,
                'description' => $data->description,
                'amount' => $data->amount,
                'category' => $data->category,
                'status' => RequestStatus::Draft,
                'requester_id' => $author->id,
            ]);

            // код строим от id, а не от количества заявок, так он стабильный
            $request->update([
                'code' => sprintf('REQ-%s-%04d', $request->created_at->format('Y'), $request->id),
            ]);

            $this->audit->log($request, $author, AuditAction::Created);

            return $request;
        });
    }
}
