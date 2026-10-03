@extends('layouts.app')

@section('title', 'Create Subscription')
@section('breadcrumb', 'Customer / Subscription / Create')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Subscription</h1>
        <p class="page-subtitle">Configure a subscription for {{ $customer->name }}.</p>
    </div>
</div>

<div class="card form-card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Subscription Details</h2>
            <div class="card-subtitle">Select a plan and subscription start date.</div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customers.subscriptions.store', $customer) }}">
        @csrf

        <div class="form-group">
            <label for="plan_id" class="form-label">Plan</label>
            <select id="plan_id" name="plan_id" class="form-select" required>
                <option value="">Select Plan</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} — {{ $plan->currency }} {{ number_format($plan->base_price, 2) }} / {{ $plan->billing_cycle }}
                    </option>
                @endforeach
            </select>
            @error('plan_id') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="started_at" class="form-label">Start Date & Time</label>
            <input id="started_at" type="datetime-local" name="started_at" class="form-input" value="{{ old('started_at') }}" required>
            @error('started_at') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Subscription</button>
        </div>
    </form>
</div>
@endsection
