<?php

namespace Tests\Feature;

use App\Models\Merchant;
use App\Models\Plan;
use App\Services\PlanPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlanPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_pricing_is_cached(): void
    {
        Cache::flush();

        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Starter',
            'base_price' => 10,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $service = app(PlanPricingService::class);

        $first = $service->getPlan(
            $merchant->id,
            $plan->id
        );

        $plan->update([
            'base_price' => 20,
        ]);

        $second = $service->getPlan(
            $merchant->id,
            $plan->id
        );

        $this->assertSame($plan->id, $first->id);
        $this->assertSame($plan->id, $second->id);
        $this->assertEquals('20.0000', $second->base_price);
    }

    public function test_cache_is_invalidated_when_plan_is_updated(): void
    {
        Cache::flush();

        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Starter',
            'base_price' => 10,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $service = app(PlanPricingService::class);

        // Populate cache with the original price.
        $cachedPlan = $service->getPlan(
            $merchant->id,
            $plan->id
        );

        $this->assertEquals('10.0000', $cachedPlan->base_price);

        // Change the pricing.
        $plan->update([
            'base_price' => 25,
            'overage_rate' => 0.05,
        ]);

        // The model event should have removed the old cache.
        $freshPlan = $service->getPlan(
            $merchant->id,
            $plan->id
        );

        $this->assertEquals('25.0000', $freshPlan->base_price);
        $this->assertEquals('0.050000', $freshPlan->overage_rate);
    }
}