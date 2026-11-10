<?php

namespace App\Listeners;

use App\Events\RequestSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// в демо пишем в лог, в проде тут был бы мейл или телеграм, поэтому слушатель сразу queued
class NotifyApprover implements ShouldQueue
{
    public function handle(RequestSubmitted $event): void
    {
        Log::info('Approval requested', [
            'request' => $event->request->code,
            'approver' => $event->approver->email,
        ]);
    }
}
