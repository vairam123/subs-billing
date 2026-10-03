<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Subscription Billing')</title>

    <style>
        :root {
            --sidebar: #111827;
            --sidebar-hover: #1f2937;
            --sidebar-active: #2563eb;
            --background: #f3f4f6;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #059669;
            --danger: #dc2626;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            background: var(--background);
            color: var(--text);
        }

        a {
            text-decoration: none;
        }

        /* =========================
           Application Shell
        ========================= */

        .app-shell {
            min-height: 100vh;
            display: flex;
        }

        /* =========================
           Sidebar
        ========================= */

        .sidebar {
            width: 250px;
            min-height: 100vh;
            background: var(--sidebar);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 20;
        }

        .brand {
            height: 72px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .brand-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 11px;
        }

        .brand-text {
            font-size: 15px;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-subtitle {
            display: block;
            font-size: 11px;
            color: #9ca3af;
            font-weight: 400;
            margin-top: 3px;
        }

        .sidebar-content {
            padding: 22px 14px;
            flex: 1;
        }

        .nav-section {
            margin-bottom: 28px;
        }

        .nav-title {
            color: #6b7280;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0 12px;
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            margin-bottom: 3px;
            border-radius: 7px;
            color: #d1d5db;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: white;
        }

        .nav-link.active {
            background: var(--sidebar-active);
            color: white;
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 15px;
        }

        .merchant-context {
            margin-top: 10px;
            padding: 10px 12px;
            border-left: 2px solid #374151;
        }

        .merchant-context-title {
            color: #9ca3af;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 7px;
        }

        .merchant-context-name {
            color: white;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        /* =========================
           Main Content
        ========================= */

        .main-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }

        .topbar {
            height: 72px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .topbar-title {
            font-size: 14px;
            color: var(--muted);
        }

        .topbar-badge {
            padding: 6px 10px;
            border-radius: 20px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 600;
        }

        main {
            padding: 32px;
            max-width: 1400px;
        }

        /* =========================
           Page Header
        ========================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
        }

        .page-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        /* =========================
           Cards
        ========================= */

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 650;
        }

        .card-subtitle {
            margin-top: 4px;
            color: var(--muted);
            font-size: 13px;
        }

        .grid {
            display: grid;
            gap: 20px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        /* =========================
           Buttons
        ========================= */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 14px;
            border-radius: 7px;
            border: 1px solid transparent;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.15s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: white;
            border-color: var(--border);
            color: #374151;
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        /* =========================
           Tables
        ========================= */

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 14px;
            background: #f9fafb;
            color: #6b7280;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 14px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: #374151;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        /* =========================
           Badges
        ========================= */

        .badge {
            display: inline-flex;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-success {
            background: #ecfdf5;
            color: #047857;
        }

        .badge-warning {
            background: #fffbeb;
            color: #b45309;
        }

        .badge-danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        /* =========================
           Forms
        ========================= */

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            background: white;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* =========================
           Alerts, Forms & Detail Views
        ========================= */

        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .alert ul {
            margin: 8px 0 0 18px;
        }

        .form-card {
            max-width: 900px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .field-error {
            margin-top: 5px;
            color: var(--danger);
            font-size: 12px;
        }

        .page-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .pagination-wrapper {
            margin-top: 18px;
        }

        .empty-state {
            color: var(--muted);
            padding: 24px 14px;
            text-align: center;
        }

        .empty-card {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px;
        }

        .empty-card h3 {
            margin-top: 0;
        }

        .empty-card p,
        .empty-card-inline span {
            color: var(--muted);
        }

        .empty-card-inline {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 24px 4px;
            color: var(--text);
        }

        .plan-card {
            display: flex;
            flex-direction: column;
        }

        .plan-card-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .plan-price {
            margin-bottom: 20px;
        }

        .plan-price .currency {
            color: var(--muted);
            font-size: 14px;
        }

        .plan-price .amount {
            font-size: 30px;
            font-weight: 700;
        }

        .plan-price .cycle {
            color: var(--muted);
            font-size: 13px;
        }

        .plan-details {
            border-top: 1px solid var(--border);
            margin-top: auto;
        }

        .plan-detail {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 13px 0;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .plan-detail span {
            color: var(--muted);
        }

        .detail-list {
            margin-top: 5px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
        }

        .detail-row:last-child {
            border-bottom: 0;
        }

        .detail-row span {
            color: var(--muted);
        }

        .stat-card {
            min-height: 130px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stat-value {
            margin-top: 9px;
            font-size: 27px;
            font-weight: 750;
        }

        .stat-help {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
        }

        .dashboard-section {
            margin-top: 20px;
        }

        /* =========================
           Responsive
        ========================= */

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .main-wrapper {
                margin-left: 210px;
                width: calc(100% - 210px);
            }

            .grid-3,
            .grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .app-shell {
                display: block;
            }

            .main-wrapper {
                margin-left: 0;
                width: 100%;
            }

            .sidebar-content {
                padding: 12px;
            }

            .nav-section {
                margin-bottom: 10px;
            }

            main {
                padding: 20px;
            }

            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 0 20px;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

@php
    $currentMerchant = request()->route('merchant');

    if (is_numeric($currentMerchant)) {
        $currentMerchant = \App\Models\Merchant::find($currentMerchant);
    }

    $isMerchantsSection = request()->routeIs('merchants.*');
    $isCustomerSection = request()->routeIs('merchants.customers.*');
    $isPlanSection = request()->routeIs('merchants.plans.*');
    $isDashboardSection = request()->is('api/merchants/*/dashboard');
@endphp

<div class="app-shell">

    {{-- Sidebar --}}
    <aside class="sidebar">

        <div class="brand">
            <div class="brand-icon">
                S
            </div>

            <div class="brand-text">
                Subscription Billing
                <span class="brand-subtitle">Usage Management</span>
            </div>
        </div>

        <div class="sidebar-content">

            <div class="nav-section">

                <div class="nav-title">
                    Workspace
                </div>

                <a
                    href="{{ route('merchants.index') }}"
                    class="nav-link {{ $isMerchantsSection && !$isCustomerSection && !$isPlanSection ? 'active' : '' }}"
                >
                    <span class="nav-icon">▦</span>
                    Merchants
                </a>

            </div>

            @if ($currentMerchant)

                <div class="nav-section">

                    <div class="nav-title">
                        Merchant
                    </div>

                    <div class="merchant-context">

                        <div class="merchant-context-title">
                            Current merchant
                        </div>

                        <div class="merchant-context-name">
                            {{ $currentMerchant->name }}
                        </div>

                    </div>

                    <a
                        href="{{ url('/api/merchants/' . $currentMerchant->id . '/dashboard') }}"
                        class="nav-link {{ $isDashboardSection ? 'active' : '' }}"
                    >
                        <span class="nav-icon">◈</span>
                        Overview
                    </a>

                    <a
                        href="{{ route('merchants.customers.index', $currentMerchant) }}"
                        class="nav-link {{ $isCustomerSection ? 'active' : '' }}"
                    >
                        <span class="nav-icon">♙</span>
                        Customers
                    </a>

                    <a
                        href="{{ route('merchants.plans.index', $currentMerchant) }}"
                        class="nav-link {{ $isPlanSection ? 'active' : '' }}"
                    >
                        <span class="nav-icon">◫</span>
                        Plans
                    </a>

                </div>

            @endif

        </div>

    </aside>

    {{-- Main application --}}
    <div class="main-wrapper">

        <header class="topbar">

            <div class="topbar-title">
                @yield('breadcrumb', 'Subscription Billing & Usage')
            </div>

            <div class="topbar-badge">
                Multi-Tenant SaaS
            </div>

        </header>

        <main>

            @yield('content')

        </main>

    </div>

</div>

@stack('scripts')

</body>
</html>