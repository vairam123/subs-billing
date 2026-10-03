@extends('layouts.app')

@section('title', 'Subscription Billing & Usage')
@section('breadcrumb', 'Workspace / Home')

@section('content')
<div class="welcome-hero">
    <div>
        <span class="badge badge-success">Billing Platform</span>
        <h1 class="page-title welcome-title">Subscription Billing & Usage</h1>
        <p class="page-subtitle welcome-subtitle">
            Manage multi-tenant merchants, subscription plans, customer usage and billing operations from one workspace.
        </p>

        <div class="page-actions welcome-actions">
            <a href="{{ route('merchants.index') }}" class="btn btn-primary">Open Merchants</a>
            <a href="{{ route('merchants.create') }}" class="btn btn-secondary">Create Merchant</a>
        </div>
    </div>
</div>

<div class="grid grid-3">
    <div class="card">
        <h2 class="card-title">Usage Metering</h2>
        <p class="card-subtitle">Capture usage events with tenant isolation, idempotency and asynchronous aggregation.</p>
    </div>

    <div class="card">
        <h2 class="card-title">Subscription Billing</h2>
        <p class="card-subtitle">Support included usage, overage billing, proration and mid-cycle plan changes.</p>
    </div>

    <div class="card">
        <h2 class="card-title">Merchant Analytics</h2>
        <p class="card-subtitle">Monitor current-cycle usage, projected overage revenue and month-over-month changes.</p>
    </div>
</div>
@endsection
