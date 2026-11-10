<?php

namespace Tests\Feature;

use App\DTO\DecisionData;
use App\Enums\RequestStatus;
use App\Http\Actions\DecideRequestAction;
use App\Http\Actions\SubmitRequestAction;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_small_purchase_is_approved_by_manager_only(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();
        User::factory()->cfo()->create();

        $request = PurchaseRequest::factory()->create([
            'requester_id' => $employee->id,
            'amount' => 45000,
        ]);

        app(SubmitRequestAction::class)->handle($request, $employee);
        $request->refresh();

        $this->assertSame(RequestStatus::Pending, $request->status);
        $this->assertSame($manager->id, $request->current_approver_id);
        $this->assertSame(1, $request->approvals()->count());

        app(DecideRequestAction::class)->handle(
            $request,
            $manager,
            new DecisionData(approve: true, comment: 'Ок', version: $request->version),
        );

        $request->refresh();

        $this->assertSame(RequestStatus::Approved, $request->status);
        $this->assertNotNull($request->decided_at);
        $this->assertNull($request->current_approver_id);
    }

    public function test_large_purchase_requires_cfo_after_manager(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();
        $cfo = User::factory()->cfo()->create();

        $request = PurchaseRequest::factory()->create([
            'requester_id' => $employee->id,
            'amount' => 250000,
        ]);

        app(SubmitRequestAction::class)->handle($request, $employee);
        $request->refresh();

        $this->assertSame(2, $request->approvals()->count());

        app(DecideRequestAction::class)->handle(
            $request,
            $manager,
            new DecisionData(true, null, $request->version),
        );

        $request->refresh();

        $this->assertSame(RequestStatus::Pending, $request->status);
        $this->assertSame($cfo->id, $request->current_approver_id);

        app(DecideRequestAction::class)->handle(
            $request,
            $cfo,
            new DecisionData(true, 'В бюджете', $request->version),
        );

        $this->assertSame(RequestStatus::Approved, $request->fresh()->status);
    }

    public function test_rejection_stops_the_chain(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();
        User::factory()->cfo()->create();

        $request = PurchaseRequest::factory()->create([
            'requester_id' => $employee->id,
            'amount' => 300000,
        ]);

        app(SubmitRequestAction::class)->handle($request, $employee);
        $request->refresh();

        app(DecideRequestAction::class)->handle(
            $request,
            $manager,
            new DecisionData(false, 'Нет бюджета', $request->version),
        );

        $request->refresh();

        $this->assertSame(RequestStatus::Rejected, $request->status);
        $this->assertSame(1, $request->approvals()->count(), 'Второй шаг должен быть удалён');
    }
}
