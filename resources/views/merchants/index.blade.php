@extends('layouts.app')

@section('title', 'Merchants')

@section('breadcrumb', 'Workspace / Merchants')

@section('content')

<div class="page-header">

    <div>
        <h1 class="page-title">
            Merchants
        </h1>

        <p class="page-subtitle">
            Manage your SaaS tenants, plans and customers.
        </p>
    </div>

    <div>
        <a
            href="{{ route('merchants.create') }}"
            class="btn btn-primary"
        >
            + Create Merchant
        </a>
    </div>

</div>

<div class="card">

    <div class="card-header">

        <div>
            <h2 class="card-title">
                All Merchants
            </h2>

            <div class="card-subtitle">
                Your registered SaaS tenants
            </div>
        </div>

    </div>

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th>Merchant</th>
                    <th>Plans</th>
                    <th>Customers</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

                @forelse ($merchants as $merchant)

                    <tr>

                        <td>
                            <strong>
                                {{ $merchant->name }}
                            </strong>
                        </td>

                        <td>
                            {{ $merchant->plans->count() }}
                        </td>

                        <td>
                            {{ $merchant->customers->count() }}
                        </td>

                        <td>
                            <span class="badge badge-success">
                                Active
                            </span>
                        </td>

                        <td style="text-align: right;">

                            <a
                                href="{{ route('merchants.show', $merchant) }}"
                                class="btn btn-secondary"
                            >
                                View
                            </a>

                            <a
                                href="{{ url('/api/merchants/' . $merchant->id . '/dashboard') }}"
                                class="btn btn-primary"
                            >
                                Dashboard
                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            No merchants found.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection