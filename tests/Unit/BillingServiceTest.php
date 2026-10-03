<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\DailyUsage;
use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_cycle_within_included_usage_charges_base_price_only(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
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

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'status' => 'active',
            'started_at' => '2026-10-01 00:00:00',
            'ended_at' => null,
        ]);

        $period = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10.00,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $period->id,
            'usage_date' => '2026-10-10',
            'total_units' => 500,
        ]);

        $invoice = app(BillingService::class)->generateInvoice(
            $subscription,
            now()->parse('2026-10-01 00:00:00'),
            now()->parse('2026-11-01 00:00:00')
        );

        $this->assertInstanceOf(Invoice::class, $invoice);

        $this->assertSame(10.0000, (float) $invoice->subtotal);
        $this->assertSame(10.0000, (float) $invoice->total);

        $this->assertCount(1, $invoice->items);

        $item = $invoice->items->first();

        $this->assertSame('base', $item->type);
        //$this->assertSame(500, $item->usage_units);
        $this->assertSame(0, $item->usage_units);
        $this->assertSame(10.0000, (float) $item->amount);
    }

    public function test_overage_usage_charges_overage_amount(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
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

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'status' => 'active',
            'started_at' => '2026-10-01 00:00:00',
            'ended_at' => null,
        ]);

        $period = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10.00,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $period->id,
            'usage_date' => '2026-10-10',
            'total_units' => 1200,
        ]);

        $invoice = app(BillingService::class)->generateInvoice(
            $subscription,
            now()->parse('2026-10-01 00:00:00'),
            now()->parse('2026-11-01 00:00:00')
        );

        $this->assertSame(14.0000, (float) $invoice->subtotal);
        $this->assertSame(14.0000, (float) $invoice->total);

        $this->assertCount(2, $invoice->items);

        $baseItem = $invoice->items
            ->firstWhere('type', 'base');

        $overageItem = $invoice->items
            ->firstWhere('type', 'overage');

        $this->assertNotNull($baseItem);
        $this->assertNotNull($overageItem);

        $this->assertSame(10.0000, (float) $baseItem->amount);

        $this->assertSame(200, $overageItem->usage_units);
        $this->assertSame(0.020000, (float) $overageItem->unit_price);
        $this->assertSame(4.0000, (float) $overageItem->amount);
    }

    public function test_mid_cycle_plan_change_prorates_both_subscription_periods_and_uses_period_specific_usage(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        $oldPlan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic Plan',
            'base_price' => 10.00,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $newPlan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Pro Plan',
            'base_price' => 20.00,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 2000,
            'overage_rate' => 0.01,
        ]);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'status' => 'active',
            'started_at' => '2026-10-01 00:00:00',
            'ended_at' => null,
        ]);

        // Old plan: October 1 to October 16
        $oldPeriod = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $oldPlan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-10-16 00:00:00',
            'base_price' => 10.00,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        // New plan: October 16 to November 1
        $newPeriod = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $newPlan->id,
            'starts_at' => '2026-10-16 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 20.00,
            'included_units' => 2000,
            'overage_rate' => 0.01,
        ]);

        // Usage before the plan change.
        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $oldPeriod->id,
            'usage_date' => '2026-10-10',
            'total_units' => 800,
        ]);

        // Usage after the plan change.
        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $newPeriod->id,
            'usage_date' => '2026-10-20',
            'total_units' => 1500,
        ]);

        $invoice = app(BillingService::class)->generateInvoice(
            $subscription,
            now()->parse('2026-10-01 00:00:00'),
            now()->parse('2026-11-01 00:00:00')
        );

        /*
         * Billing period = 31 days.
         *
         * OLD PERIOD
         * 15 days of the billing period.
         *
         * Base:
         * 10 × 15/31 = 4.8387
         *
         * Included units:
         * 1000 × 15/31 = 483
         *
         * Usage:
         * 800
         *
         * Overage:
         * 800 - 483 = 317
         *
         * Overage charge:
         * 317 × 0.02 = 6.34
         *
         *
         * NEW PERIOD
         * 16 days of the billing period.
         *
         * Base:
         * 20 × 16/31 = 10.3226
         *
         * Included units:
         * 2000 × 16/31 = 1032
         *
         * Usage:
         * 1500
         *
         * Overage:
         * 1500 - 1032 = 468
         *
         * Overage charge:
         * 468 × 0.01 = 4.68
         *
         *
         * TOTAL
         * 4.8387 + 6.34 + 10.3226 + 4.68
         * = 26.1813
         */

        $this->assertSame(26.1813, (float) $invoice->subtotal);
        $this->assertSame(26.1813, (float) $invoice->total);

        // Two base items + two overage items.
        $this->assertCount(4, $invoice->items);

        // Find the old plan's base charge.
        $oldBaseItem = $invoice->items->first(
            fn ($item) =>
                $item->type === 'base' &&
                $item->subscription_period_id === $oldPeriod->id
        );

        // Find the new plan's base charge.
        $newBaseItem = $invoice->items->first(
            fn ($item) =>
                $item->type === 'base' &&
                $item->subscription_period_id === $newPeriod->id
        );

        // Find the old plan's overage charge.
        $oldOverageItem = $invoice->items->first(
            fn ($item) =>
                $item->type === 'overage' &&
                $item->subscription_period_id === $oldPeriod->id
        );

        // Find the new plan's overage charge.
        $newOverageItem = $invoice->items->first(
            fn ($item) =>
                $item->type === 'overage' &&
                $item->subscription_period_id === $newPeriod->id
        );

        $this->assertNotNull($oldBaseItem);
        $this->assertNotNull($newBaseItem);
        $this->assertNotNull($oldOverageItem);
        $this->assertNotNull($newOverageItem);

        // Old plan base charge.
        $this->assertEqualsWithDelta(
            4.8387,
            (float) $oldBaseItem->amount,
            0.0001
        );

        // New plan base charge.
        $this->assertEqualsWithDelta(
            10.3226,
            (float) $newBaseItem->amount,
            0.0001
        );

        // Old plan overage.
        $this->assertSame(317, $oldOverageItem->usage_units);

        $this->assertEqualsWithDelta(
            6.34,
            (float) $oldOverageItem->amount,
            0.0001
        );

        // New plan overage.
        $this->assertSame(468, $newOverageItem->usage_units);

        $this->assertEqualsWithDelta(
            4.68,
            (float) $newOverageItem->amount,
            0.0001
        );
    }

    public function test_generating_same_invoice_period_twice_returns_existing_invoice(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic',
            'base_price' => 10,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'status' => 'active',
            'started_at' => '2026-10-01 00:00:00',
            'ended_at' => null,
        ]);

        $period = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $period->id,
            'usage_date' => '2026-10-15',
            'total_units' => 500,
        ]);

        $billingService = app(BillingService::class);

        $start = now()->parse('2026-10-01 00:00:00');
        $end = now()->parse('2026-11-01 00:00:00');

        $firstInvoice = $billingService->generateInvoice(
            $subscription,
            $start,
            $end
        );

        $secondInvoice = $billingService->generateInvoice(
            $subscription,
            $start,
            $end
        );

        $this->assertSame(
            $firstInvoice->id,
            $secondInvoice->id
        );

        $this->assertDatabaseCount('invoices', 1);

        $this->assertDatabaseCount('invoice_items', 1);

        $this->assertEquals(
            10.0000,
            (float) $secondInvoice->total
        );
    }

    public function test_usage_exactly_at_included_limit_has_no_overage(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'exact@example.com',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic',
            'base_price' => 10,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'status' => 'active',
            'started_at' => '2026-10-01 00:00:00',
            'ended_at' => null,
        ]);

        $period = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $period->id,
            'usage_date' => '2026-10-15',
            'total_units' => 1000,
        ]);

        $invoice = app(BillingService::class)->generateInvoice(
            $subscription,
            now()->parse('2026-10-01 00:00:00'),
            now()->parse('2026-11-01 00:00:00')
        );

        $this->assertEquals(10.0000, (float) $invoice->total);

        $this->assertDatabaseMissing('invoice_items', [
            'invoice_id' => $invoice->id,
            'type' => 'overage',
        ]);
    }

    public function test_one_unit_above_included_limit_creates_one_overage_unit(): void
    {
        $merchant = Merchant::create([
            'name' => 'Test Merchant',
        ]);

        $customer = Customer::create([
            'merchant_id' => $merchant->id,
            'name' => 'Test Customer',
            'email' => 'one-over@example.com',
        ]);

        $plan = Plan::create([
            'merchant_id' => $merchant->id,
            'name' => 'Basic',
            'base_price' => 10,
            'currency' => 'USD',
            'billing_cycle' => 'monthly',
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        $subscription = Subscription::create([
            'customer_id' => $customer->id,
            'status' => 'active',
            'started_at' => '2026-10-01 00:00:00',
            'ended_at' => null,
        ]);

        $period = SubscriptionPeriod::create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'starts_at' => '2026-10-01 00:00:00',
            'ends_at' => '2026-11-01 00:00:00',
            'base_price' => 10,
            'included_units' => 1000,
            'overage_rate' => 0.02,
        ]);

        DailyUsage::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
            'subscription_period_id' => $period->id,
            'usage_date' => '2026-10-15',
            'total_units' => 1001,
        ]);

        $invoice = app(BillingService::class)->generateInvoice(
            $subscription,
            now()->parse('2026-10-01 00:00:00'),
            now()->parse('2026-11-01 00:00:00')
        );

        $this->assertEquals(10.0200, (float) $invoice->total);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'type' => 'overage',
            'usage_units' => 1,
        ]);
    }
}

