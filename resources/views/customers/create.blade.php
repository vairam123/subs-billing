@extends('layouts.app')

@section('title', 'Create Customer')
@section('breadcrumb', 'Merchant / Customers / Create')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Customer</h1>
        <p class="page-subtitle">Add a new customer to {{ $merchant->name }}.</p>
    </div>

    <a href="{{ route('merchants.customers.index', $merchant) }}" class="btn btn-secondary">
        ← Back to Customers
    </a>
</div>

<div class="card form-card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Customer Information</h2>
            <div class="card-subtitle">Enter the customer's basic account details.</div>
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

    <form method="POST" action="{{ route('merchants.customers.store', $merchant) }}">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Customer Name</label>
            <input id="name" type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="e.g. Acme Corporation" required>
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="customer@example.com" required>
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('merchants.customers.index', $merchant) }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Customer</button>
        </div>
    </form>
</div>
@endsection
