<?php

namespace Tests\Unit;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\ApprovalChainResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalChainResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_below_threshold_goes_to_manager(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();
        User::factory()->cfo()->create();

        $request = PurchaseRequest::factory()->make([
            'requester_id' => $employee->id,
            'amount' => PurchaseRequest::CFO_THRESHOLD - 1,
        ]);

        $chain = app(ApprovalChainResolver::class)->resolve($request);

        $this->assertCount(1, $chain);
        $this->assertSame($manager->id, $chain[0]->id);
    }

    public function test_threshold_adds_cfo(): void
    {
        $employee = User::factory()->create();
        $manager = User::factory()->manager()->create();
        $cfo = User::factory()->cfo()->create();

        $request = PurchaseRequest::factory()->make([
            'requester_id' => $employee->id,
            'amount' => PurchaseRequest::CFO_THRESHOLD,
        ]);

        $chain = app(ApprovalChainResolver::class)->resolve($request);

        $this->assertCount(2, $chain);
        $this->assertSame($manager->id, $chain[0]->id);
        $this->assertSame($cfo->id, $chain[1]->id);
    }

    public function test_author_is_excluded_from_the_chain(): void
    {
        User::factory()->create();
        $manager = User::factory()->manager()->create();
        $cfo = User::factory()->cfo()->create();

        $request = PurchaseRequest::factory()->make([
            'requester_id' => $manager->id,
            'amount' => 50000,
        ]);

        $chain = app(ApprovalChainResolver::class)->resolve($request);

        $this->assertCount(1, $chain);
        $this->assertSame($cfo->id, $chain[0]->id);
    }
}
