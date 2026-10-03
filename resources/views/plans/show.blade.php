@extends('layouts.app')

@section('title', $plan->name . ' Plan Details')
@section('breadcrumb', 'Merchant / Plans / ' . $plan->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $plan->name }}</h1>
        <p class="page-subtitle">Plan configuration for {{ $merchant->name }}.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('merchants.plans.edit', [$merchant, $plan]) }}" class="btn btn-primary">Edit Plan</a>
        <a href="{{ route('merchants.plans.index', $merchant) }}" class="btn btn-secondary">← Back to Plans</a>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Pricing</h2>
                <div class="card-subtitle">Subscription billing configuration</div>
            </div>
        </div>

        <div class="detail-list">
            <div class="detail-row"><span>Base Price</span><strong>{{ $plan->currency }} {{ number_format($plan->base_price, 2) }}</strong></div>
            <div class="detail-row"><span>Billing Cycle</span><strong>{{ ucfirst($plan->billing_cycle) }}</strong></div>
            <div class="detail-row"><span>Currency</span><strong>{{ $plan->currency }}</strong></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Usage & Overage</h2>
                <div class="card-subtitle">Metered billing configuration</div>
            </div>
        </div>

        <div class="detail-list">
            <div class="detail-row"><span>Included Usage</span><strong>{{ number_format($plan->included_units) }} units</strong></div>
            <div class="detail-row"><span>Overage Rate</span><strong>{{ $plan->currency }} {{ number_format($plan->overage_rate, 4) }} / unit</strong></div>
        </div>
    </div>
</div>
@endsection
