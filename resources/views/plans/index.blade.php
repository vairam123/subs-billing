@extends('layouts.app')

@section('title', $merchant->name . ' - Plans')
@section('breadcrumb', 'Merchant / Plans')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Plans</h1>
        <p class="page-subtitle">Subscription pricing and usage limits for {{ $merchant->name }}.</p>
    </div>

    <div class="page-actions">
        <a href="{{ route('merchants.show', $merchant) }}" class="btn btn-secondary">← Merchant</a>
        <a href="{{ route('merchants.plans.create', $merchant) }}" class="btn btn-primary">+ Create Plan</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="grid grid-3">
    @forelse ($plans as $plan)
        <div class="card plan-card">
            <div class="plan-card-header">
                <div>
                    <h2 class="card-title">{{ $plan->name }}</h2>
                    <div class="card-subtitle">{{ ucfirst($plan->billing_cycle) }} billing</div>
                </div>
                <span class="badge badge-success">Active</span>
            </div>

            <div class="plan-price">
                <span class="currency">{{ $plan->currency }}</span>
                <span class="amount">{{ number_format($plan->base_price, 2) }}</span>
                <span class="cycle">/ {{ $plan->billing_cycle }}</span>
            </div>

            <div class="plan-details">
                <div class="plan-detail">
                    <span>Included Usage</span>
                    <strong>{{ number_format($plan->included_units) }}</strong>
                </div>
                <div class="plan-detail">
                    <span>Overage Rate</span>
                    <strong>{{ $plan->currency }} {{ number_format($plan->overage_rate, 4) }}</strong>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('merchants.plans.show', [$merchant, $plan]) }}" class="btn btn-secondary">View</a>
                <a href="{{ route('merchants.plans.edit', [$merchant, $plan]) }}" class="btn btn-primary">Edit</a>
            </div>
        </div>
    @empty
        <div class="card empty-card">
            <h3>No plans yet</h3>
            <p>Create your first subscription plan for this merchant.</p>
            <a href="{{ route('merchants.plans.create', $merchant) }}" class="btn btn-primary">+ Create Plan</a>
        </div>
    @endforelse
</div>
@endsection
