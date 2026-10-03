<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DailyUsage;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_dashboard_returns_required_usage_metrics(): void
    {
        $merchant = Merchant::create([
            'name' => 'Dashboard Merchant',
        ]);

        $customer1 = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Customer One',
            'email' => 'one@example.com',
        ]);

        $customer2 = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Customer Two',
            'email' => 'two@example.com',
        ]);

        $customer3 = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Customer Three',
            'email' => 'three@example.com',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic Plan',
            'base_price' => 10.00,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $subscription1 = Subscription::create([
            'customer_id' => $customer1->id,
            'status' => 'active',
            'started_at' => '2026-09-01 00:00:00',
            'ended_at' => null,
        ]);

        $period1 = SubscriptionPeriod::create([
            'subscription_id' => $subscription1->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-09-01 00:00:00',
            'ends_at' => '2026-10-16 00:00:00',
            'base_price' => 10.00,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $subscription2 = Subscription::create([
            'customer_id' => $customer2->id,
            'status' => 'active',
            'started_at' => '2026-09-01 00:00:00',
            'ended_at' => null,
        ]);

        $period2 = SubscriptionPeriod::create([
            'subscription_id' => $subscription2->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-09-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10.00,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        /*
         * Current month usage.
         *
         * Customer 1 = 1500
         * Customer 2 = 800
         * Customer 3 = 0
         */
        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer1->id,
            'subscription_period_id' => $period1->id,
            'usage_date' => '2026-10-10',
            'total_units' => 1500,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer2->id,
            'subscription_period_id' => $period2->id,
            'usage_date' => '2026-10-10',
            'total_units' => 800,
        ]);

        /*
         * Previous month usage for customer 1.
         *
         * 1000 -> 400 means a 60% drop.
         */
        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer1->id,
            'subscription_period_id' => $period1->id,
            'usage_date' => '2026-09-10',
            'total_units' => 1000,
        ]);

        /*
         * Customer 3 has previous-month usage but no current-month
         * usage, which represents a 100% drop.
         */
        $subscription3 = Subscription::create([
            'customer_id' => $customer3->id,
            'status' => 'active',
            'started_at' => '2026-09-01 00:00:00',
            'ended_at' => null,
        ]);

        $period3 = SubscriptionPeriod::create([
            'subscription_id' => $subscription3->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-09-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10.00,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer3->id,
            'subscription_period_id' => $period3->id,
            'usage_date' => '2026-09-10',
            'total_units' => 500,
        ]);

        $response = $this->getJson(
            "/api/merchants/{$merchant->id}/dashboard"
        );

        $response
            ->assertOk()
            ->assertJsonStructure([
                'merchant_id',
                'period' => [
                    'current_month_start',
                    'current_month_end',
                ],
                'top_customers_by_usage',
                'projected_overage_revenue',
                'customers_with_usage_drop_over_50_percent',
            ]);

        $response->assertJsonPath(
            'merchant_id',
            $merchant->id
        );

        $response->assertJsonPath(
            'top_customers_by_usage.0.customer_id',
            $customer1->id
        );

        $response->assertJsonPath(
            'top_customers_by_usage.0.usage_units',
            1500
        );

        $response->assertJsonPath(
            'top_customers_by_usage.1.customer_id',
            $customer2->id
        );

        /*
         * Customer 1:
         * 1500 usage - 1000 included = 500 overage
         *
         * 500 × 0.02 = $10
         */
        $response->assertJsonPath(
            'projected_overage_revenue',
            10
        );

        $response->assertJsonFragment([
            'customer_id' => $customer3->id,
            'previous_month_usage' => 500,
            'current_month_usage' => 0,
            'drop_percentage' => 100,
        ]);

        $response->assertJsonFragment([
            'customer_id' => $customer3->id,
            'previous_month_usage' => 500,
            'current_month_usage' => 0,
            'drop_percentage' => 100,
        ]);
    }
}