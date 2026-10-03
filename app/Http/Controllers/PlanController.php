<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
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

    public function create(Merchant $merchant): View
    {
        return view('plans.create', [
            'merchant' => $merchant,
        ]);
    }

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