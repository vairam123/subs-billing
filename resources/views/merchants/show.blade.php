@extends('layouts.app')

@section('title', $merchant->name)
@section('breadcrumb', 'Merchant / ' . $merchant->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $merchant->name }}</h1>
        <p class="page-subtitle">Merchant ID: {{ $merchant->id }}</p>
    </div>

    <div class="page-actions">
        <a href="{{ url('/api/merchants/' . $merchant->id . '/dashboard') }}" class="btn btn-primary">Dashboard</a>
        <a href="{{ route('merchants.customers.index', $merchant) }}" class="btn btn-secondary">Customers</a>
        <a href="{{ route('merchants.plans.index', $merchant) }}" class="btn btn-secondary">Plans</a>
    </div>
</div>

<div class="grid grid-3 merchant-stats">
    <div class="card stat-card">
        <div class="stat-label">Plans</div>
        <div class="stat-value">{{ $merchant->plans->count() }}</div>
    </div>

    <div class="card stat-card">
        <div class="stat-label">Customers</div>
        <div class="stat-value">{{ $merchant->customers->count() }}</div>
    </div>

    <div class="card stat-card">
        <div class="stat-label">Status</div>
        <div class="stat-value"><span class="badge badge-success">Active</span></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Subscription Plans</h2>
            <div class="card-subtitle">Pricing and usage configuration for this merchant.</div>
        </div>
        <a href="{{ route('merchants.plans.create', $merchant) }}" class="btn btn-primary">+ Create Plan</a>
    </div>

    @if ($merchant->plans->isEmpty())
        <div class="empty-card-inline">
            <strong>No plans created yet.</strong>
            <span>Create a plan to start assigning subscriptions.</span>
        </div>
    @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Price</th>
                        <th>Included Usage</th>
                        <th>Overage</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($merchant->plans as $plan)
                        <tr>
                            <td><strong>{{ $plan->name }}</strong></td>
                            <td>{{ $plan->currency }} {{ number_format($plan->base_price, 2) }} / {{ $plan->billing_cycle }}</td>
                            <td>{{ number_format($plan->included_units) }} units</td>
                            <td>{{ $plan->currency }} {{ number_format($plan->overage_rate, 4) }} / unit</td>
                            <td style="text-align:right;">
                                <a href="{{ route('merchants.plans.show', [$merchant, $plan]) }}" class="btn btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
