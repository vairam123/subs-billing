@extends('layouts.app')

@section('title', 'Merchant Dashboard')
@section('breadcrumb', 'Merchant / Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Merchant Dashboard</h1>
        <p class="page-subtitle">
            Usage period: {{ $period['current_month_start'] }} to {{ $period['current_month_end'] }}
        </p>
    </div>

    <span class="badge badge-success">Current Cycle</span>
</div>

<div class="grid grid-3 dashboard-stats">
    <div class="card stat-card">
        <div class="stat-label">Projected Overage Revenue</div>
        <div class="stat-value">${{ number_format($projected_overage_revenue, 4) }}</div>
        <div class="stat-help">Projected for the current billing cycle</div>
    </div>

    <div class="card stat-card">
        <div class="stat-label">Top Customers</div>
        <div class="stat-value">{{ $top_customers_by_usage->count() }}</div>
        <div class="stat-help">Customers returned in the top-5 usage view</div>
    </div>

    <div class="card stat-card">
        <div class="stat-label">Usage Drops &gt;50%</div>
        <div class="stat-value">{{ $customers_with_usage_drop_over_50_percent->count() }}</div>
        <div class="stat-help">Compared with the previous month</div>
    </div>
</div>

<div class="card dashboard-section">
    <div class="card-header">
        <div>
            <h2 class="card-title">Top 5 Customers by Usage</h2>
            <div class="card-subtitle">Highest recorded usage during the current month.</div>
        </div>
    </div>

    @if ($top_customers_by_usage->isEmpty())
        <div class="empty-card-inline">
            <strong>No usage recorded this month.</strong>
            <span>Usage data will appear here after events are aggregated.</span>
        </div>
    @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th style="text-align:right;">Usage Units</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($top_customers_by_usage as $customer)
                        <tr>
                            <td><strong>{{ $customer['customer_name'] }}</strong></td>
                            <td>{{ $customer['email'] }}</td>
                            <td style="text-align:right;">{{ number_format($customer['usage_units']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card dashboard-section">
    <div class="card-header">
        <div>
            <h2 class="card-title">Customers With More Than 50% Usage Drop</h2>
            <div class="card-subtitle">Customers whose current-month usage is materially lower than the previous month.</div>
        </div>
    </div>

    @if ($customers_with_usage_drop_over_50_percent->isEmpty())
        <div class="empty-card-inline">
            <strong>No customers have dropped more than 50%.</strong>
            <span>No significant month-over-month usage drop is currently detected.</span>
        </div>
    @else
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Previous Month</th>
                        <th>Current Month</th>
                        <th style="text-align:right;">Drop</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers_with_usage_drop_over_50_percent as $customer)
                        <tr>
                            <td><strong>{{ $customer['customer_name'] }}</strong></td>
                            <td>{{ $customer['email'] }}</td>
                            <td>{{ number_format($customer['previous_month_usage']) }}</td>
                            <td>{{ number_format($customer['current_month_usage']) }}</td>
                            <td style="text-align:right;"><span class="badge badge-danger">{{ number_format($customer['drop_percentage'], 2) }}%</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
