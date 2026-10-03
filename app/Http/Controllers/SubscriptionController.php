<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function create(Customer $customer): View
    {
        $plans = Plan::where('merchant_id', $customer->merchant_id)
            ->orderBy('name')
            ->get();

        return view('subscriptions.create', [
            'customer' => $customer,
            'plans' => $plans,
        ]);
    }

    public function store(
        Request $request,
        Customer $customer
    ): RedirectResponse {
        $validated = $request->validate([
            'plan_id' => [
                'required',
                'integer',
            ],
            'started_at' => [
                'required',
                'date',
            ],
        ]);

        /*
         * Important:
         * The plan must belong to the same merchant
         * as the customer.
         */
        $plan = Plan::where('merchant_id', $customer->merchant_id)
            ->findOrFail($validated['plan_id']);

        $startedAt = Carbon::parse(
            $validated['started_at']
        );

        DB::transaction(function () use (
            $customer,
            $plan,
            $startedAt
        ) {
            $endedAt = $this->calculateCycleEnd(
                $startedAt,
                $plan->billing_cycle
            );

            $subscription = $customer->subscriptions()->create([
                'status' => 'active',
                'started_at' => $startedAt,
            ]);

            $subscription->periods()->create([
                'plan_id' => $plan->id,
                'starts_at' => $startedAt,
                'ends_at' => $endedAt,

                // Pricing snapshot
                'base_price' => $plan->base_price,
                'included_units' => $plan->included_units,
                'overage_rate' => $plan->overage_rate,
            ]);
        });

        return redirect()
            ->route(
                'merchants.customers.index',
                $customer->merchant_id
            )
            ->with(
                'success',
                'Subscription created successfully.'
            );
    }

    private function calculateCycleEnd(
        Carbon $startedAt,
        string $billingCycle
    ): Carbon {
        return match ($billingCycle) {
            'monthly' => $startedAt->copy()->addMonth(),
            'yearly' => $startedAt->copy()->addYear(),

            default => throw new \InvalidArgumentException(
                'Unsupported billing cycle.'
            ),
        };
    }
}