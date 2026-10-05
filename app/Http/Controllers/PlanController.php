<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    /**
    * List the plans belonging to the merchant.
    */
    public function index(Merchant $merchant): View
    {
        $plans = $merchant->plans()
            ->latest()
            ->get();

        return view('plans.index', [
            'merchant' => $merchant,
            'plans' => $plans,
        ]);
    }

    /**
    * Display the form for creating a merchant plan.
    */
    public function create(Merchant $merchant): View
    {
        return view('plans.create', [
            'merchant' => $merchant,
        ]);
    }

    /**
    * Validate and create a billing plan for the merchant.
    *
    * A plan defines the base price, billing cycle, included units, and
    * overage rate used by subscription billing.
    */
    public function store(
        Request $request,
        Merchant $merchant
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'included_units' => ['required', 'integer', 'min:0'],
            'overage_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $merchant->plans()->create($validated);

        return redirect()
            ->route('merchants.plans.index', $merchant)
            ->with('success', 'Plan created successfully.');
    }

    /**
    * Display a merchant plan after enforcing merchant ownership.
    */
    public function show(
        Merchant $merchant,
        Plan $plan
    ): View {
        $this->ensurePlanBelongsToMerchant($merchant, $plan);

        return view('plans.show', [
            'merchant' => $merchant,
            'plan' => $plan,
        ]);
    }

    /**
    * Display the edit form after enforcing merchant ownership.
    */
    public function edit(
        Merchant $merchant,
        Plan $plan
    ): View {
        $this->ensurePlanBelongsToMerchant($merchant, $plan);

        return view('plans.edit', [
            'merchant' => $merchant,
            'plan' => $plan,
        ]);
    }

    /**
    * Validate and update a merchant's plan pricing and usage limits.
    *
    * Plan model events handle invalidation of cached pricing after changes.
    */
    public function update(
        Request $request,
        Merchant $merchant,
        Plan $plan
    ): RedirectResponse {
        $this->ensurePlanBelongsToMerchant($merchant, $plan);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'included_units' => ['required', 'integer', 'min:0'],
            'overage_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $plan->update($validated);

        return redirect()
            ->route('merchants.plans.show', [
                'merchant' => $merchant,
                'plan' => $plan,
            ])
            ->with('success', 'Plan updated successfully.');
    }

    /**
    * Prevent a plan belonging to another merchant from being accessed
    * through the current merchant context.
    */
    private function ensurePlanBelongsToMerchant(
        Merchant $merchant,
        Plan $plan
    ): void {
        abort_unless(
            $plan->merchant_id === $merchant->id,
            404
        );
    }
}