@extends('layouts.app')

@section('title', $merchant->name . ' - Customers')
@section('breadcrumb', 'Merchant / Customers')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Customers</h1>
        <p class="page-subtitle">Manage customers for {{ $merchant->name }}.</p>
    </div>

    <a href="{{ route('merchants.customers.create', $merchant) }}" class="btn btn-primary">
        + Create Customer
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Customer Directory</h2>
            <div class="card-subtitle">Customers belonging to this merchant</div>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td><strong>{{ $customer->name }}</strong></td>
                        <td>{{ $customer->email }}</td>
                        <td><span class="badge badge-success">Active</span></td>
                        <td style="text-align:right;">
                            <a href="{{ route('customers.subscriptions.create', $customer) }}" class="btn btn-secondary">
                                Subscribe
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No customers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($customers, 'links'))
        <div class="pagination-wrapper">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection
