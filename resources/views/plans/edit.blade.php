@extends('layouts.app')

@section('title', 'Edit ' . $plan->name)
@section('breadcrumb', 'Merchant / Plans / Edit')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Edit Plan</h1>
        <p class="page-subtitle">Update {{ $plan->name }} for {{ $merchant->name }}.</p>
    </div>
    <a href="{{ route('merchants.plans.show', [$merchant, $plan]) }}" class="btn btn-secondary">← Back to Plan</a>
</div>

<div class="card form-card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Plan Configuration</h2>
            <div class="card-subtitle">Changes apply to future subscription billing calculations.</div>
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

    <form method="POST" action="{{ route('merchants.plans.update', [$merchant, $plan]) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-2">
            <div class="form-group">
                <label for="name" class="form-label">Plan Name</label>
                <input id="name" type="text" name="name" class="form-input" value="{{ old('name', $plan->name) }}" required>
                @error('name') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="currency" class="form-label">Currency</label>
                <input id="currency" type="text" name="currency" maxlength="3" class="form-input" value="{{ old('currency', $plan->currency) }}" required>
                @error('currency') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="base_price" class="form-label">Base Price</label>
                <input id="base_price" type="number" name="base_price" step="0.0001" min="0" class="form-input" value="{{ old('base_price', $plan->base_price) }}" required>
                @error('base_price') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="billing_cycle" class="form-label">Billing Cycle</label>
                <select id="billing_cycle" name="billing_cycle" class="form-select" required>
                    <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
                @error('billing_cycle') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="included_units" class="form-label">Included Usage Units</label>
                <input id="included_units" type="number" name="included_units" min="0" class="form-input" value="{{ old('included_units', $plan->included_units) }}" required>
                @error('included_units') <div class="field-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="overage_rate" class="form-label">Overage Rate Per Unit</label>
                <input id="overage_rate" type="number" name="overage_rate" step="0.000001" min="0" class="form-input" value="{{ old('overage_rate', $plan->overage_rate) }}" required>
                @error('overage_rate') <div class="field-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('merchants.plans.show', [$merchant, $plan]) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Plan</button>
        </div>
    </form>
</div>
@endsection
