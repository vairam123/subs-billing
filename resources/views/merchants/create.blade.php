@extends('layouts.app')

@section('title', 'Create Merchant')
@section('breadcrumb', 'Workspace / Merchants / Create')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Create Merchant</h1>
        <p class="page-subtitle">Register a new SaaS tenant.</p>
    </div>

    <a href="{{ route('merchants.index') }}" class="btn btn-secondary">← Back to Merchants</a>
</div>

<div class="card form-card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Merchant Information</h2>
            <div class="card-subtitle">Create the tenant that will own plans, customers and usage.</div>
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

    <form method="POST" action="{{ route('merchants.store') }}">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Merchant Name</label>
            <input id="name" type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="e.g. Acme SaaS" required>
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="form-actions">
            <a href="{{ route('merchants.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Merchant</button>
        </div>
    </form>
</div>
@endsection
