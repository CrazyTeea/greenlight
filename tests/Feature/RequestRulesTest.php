<?php

namespace Tests\Feature;

use App\DTO\DecisionData;
use App\Enums\ApprovalDecision;
use App\Enums\RequestStatus;
use App\Exceptions\Domain\FourEyesException;
use App\Exceptions\Domain\StaleRequestException;
use App\Http\Actions\DecideRequestAction;
use App\Http\Actions\SubmitRequestAction;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_cannot_decide_own_request(): void
    {
        $manager = User::factory()->manager()->create();

        $request = PurchaseRequest::factory()->create([
            'requester_id' => $manager->id,
            'status' => RequestStatus::Pending,
            'current_approver_id' => $manager->id,
            'version' => 1,
        ]);

        $request->approvals()->create([
            'step' => 1,
            'approver_id' => $manager->id,
            'decision' => ApprovalDecision::Pending,
        ]);

        $this->expectException(FourEyesException::class);

        app(DecideRequestAction::class)->handle(
            $request,
            $manager,
            new DecisionData(true, null, 1),
        );
    }

    public function test_stale_version_is_rejected(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();

        $request = PurchaseRequest::factory()->create([
            'requester_id' => $employee->id,
            'amount' => 10000,
        ]);

        app(SubmitRequestAction::class)->handle($request, $employee);
        $request->refresh();

        $actualVersion = $request->version;

        app(DecideRequestAction::class)->handle(
            $request,
            $manager,
            new DecisionData(true, null, $actualVersion),
        );

        $this->expectException(StaleRequestException::class);

        app(DecideRequestAction::class)->handle(
            $request->fresh(),
            $manager,
            new DecisionData(true, null, $actualVersion),
        );
    }

    public function test_employee_sees_only_own_requests(): void
    {
        $employee = User::factory()->create();
        $other = User::factory()->create();

        PurchaseRequest::factory()->create(['requester_id' => $employee->id]);
        PurchaseRequest::factory()->create(['requester_id' => $other->id]);

        $this->assertSame(1, PurchaseRequest::query()->visibleTo($employee)->count());
    }

    public function test_manager_can_see_every_request(): void
    {
        $manager = User::factory()->manager()->create();
        $other = User::factory()->create();

        PurchaseRequest::factory()->count(3)->create(['requester_id' => $other->id]);

        $this->assertSame(3, PurchaseRequest::query()->visibleTo($manager)->count());
    }

    public function test_http_flow_for_third_party_is_forbidden(): void
    {
        $employee = User::factory()->create();
        $other = User::factory()->create();

        $request = PurchaseRequest::factory()->create(['requester_id' => $other->id]);

        $this->actingAs($employee)
            ->get("/requests/{$request->id}")
            ->assertForbidden();
    }

    public function test_rejection_requires_comment(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();

        $request = PurchaseRequest::factory()->pending()->create([
            'requester_id' => $employee->id,
            'current_approver_id' => $manager->id,
            'version' => 1,
        ]);

        $request->approvals()->create([
            'step' => 1,
            'approver_id' => $manager->id,
            'decision' => ApprovalDecision::Pending,
        ]);

        $this->actingAs($manager)
            ->post("/requests/{$request->id}/decide", [
                'approve' => false,
                'comment' => '',
                'version' => 1,
            ])
            ->assertSessionHasErrors('comment');
    }

    public function test_author_can_cancel_pending_request(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();

        $request = PurchaseRequest::factory()->create([
            'requester_id' => $employee->id,
            'amount' => 12000,
        ]);

        app(SubmitRequestAction::class)->handle($request, $employee);
        $request->refresh();

        $this->actingAs($employee)
            ->post("/requests/{$request->id}/cancel")
            ->assertRedirect();

        $this->assertSame(RequestStatus::Cancelled, $request->fresh()->status);
        $this->assertSame(0, $request->approvals()->count(), 'Ожидающие шаги удаляются');
    }
}
