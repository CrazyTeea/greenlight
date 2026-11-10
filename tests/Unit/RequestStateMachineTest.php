<?php

namespace Tests\Unit;

use App\Enums\RequestStatus;
use App\Services\RequestStateMachine;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RequestStateMachineTest extends TestCase
{
    /**
     * @return array<string, array{RequestStatus, RequestStatus, bool}>
     */
    public static function transitions(): array
    {
        return [
            'draft to pending' => [RequestStatus::Draft, RequestStatus::Pending, true],
            'draft to cancelled' => [RequestStatus::Draft, RequestStatus::Cancelled, true],
            'draft to approved' => [RequestStatus::Draft, RequestStatus::Approved, false],
            'pending to approved' => [RequestStatus::Pending, RequestStatus::Approved, true],
            'pending to rejected' => [RequestStatus::Pending, RequestStatus::Rejected, true],
            'pending to cancelled' => [RequestStatus::Pending, RequestStatus::Cancelled, true],
            'pending back to draft' => [RequestStatus::Pending, RequestStatus::Draft, false],
            'approved is terminal' => [RequestStatus::Approved, RequestStatus::Pending, false],
            'rejected is terminal' => [RequestStatus::Rejected, RequestStatus::Approved, false],
            'cancelled is terminal' => [RequestStatus::Cancelled, RequestStatus::Pending, false],
        ];
    }

    #[DataProvider('transitions')]
    public function test_allowed_transitions(RequestStatus $from, RequestStatus $to, bool $expected): void
    {
        $machine = new RequestStateMachine;

        $this->assertSame($expected, $machine->can($from, $to));
    }
}
