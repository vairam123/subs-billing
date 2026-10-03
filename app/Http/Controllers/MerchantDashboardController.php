<?php

namespace App\Http\Controllers;

use App\Models\DailyUsage;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class MerchantDashboardController extends Controller
{
    public function show(
        Request $request,
        Merchant $merchant
    ): JsonResponse|View {
        $monthStart = now()->startOfMonth();
        $nextMonthStart = now()->startOfMonth()->addMonth();
        $previousMonthStart = now()->startOfMonth()->subMonth();

        // ---------------------------------------------------------
        // 1. Top 5 customers by usage this month
        // ---------------------------------------------------------
        $topCustomers = DailyUsage::query()
            ->select('customer_id')
            ->selectRaw('SUM(total_units) as total_units')
            ->where('merchant_id', $merchant->id)
            ->whereDate('usage_date', '>=', $monthStart->toDateString())
            ->whereDate('usage_date', '<', $nextMonthStart->toDateString())
            ->groupBy('customer_id')
            ->orderByDesc('total_units')
            ->limit(5)
            ->with('customer:id,name,email')
            ->get()
            ->map(function ($usage) {
                return [
                    'customer_id' => $usage->customer_id,
                    'customer_name' => $usage->customer->name,
                    'email' => $usage->customer->email,
                    'usage_units' => (int) $usage->total_units,
                ];
            });

        // ---------------------------------------------------------
        // 2. Find current subscription periods
        // ---------------------------------------------------------
        $currentPeriods = $merchant->customers()
            ->with([
                'subscriptions' => function ($query) {
                    $query->where('status', 'active');
                },
                'subscriptions.periods',
            ])
            ->get()
            ->flatMap(function ($customer) use (
                $monthStart,
                $nextMonthStart
            ) {
                return $customer->subscriptions
                    ->flatMap(function ($subscription) use (
                        $monthStart,
                        $nextMonthStart
                    ) {
                        return $subscription->periods
                            ->filter(function ($period) use (
                                $monthStart,
                                $nextMonthStart
                            ) {
                                return $period->starts_at < $nextMonthStart
                                    && $period->ends_at > $monthStart;
                            });
                    });
            })
            ->values();

        // ---------------------------------------------------------
        // 3. Aggregate current-month usage by subscription period
        // ---------------------------------------------------------
        $periodIds = $currentPeriods
            ->pluck('id')
            ->unique()
            ->values();

        $usageByPeriod = collect();

        if ($periodIds->isNotEmpty()) {
            $usageByPeriod = DailyUsage::query()
                ->select('subscription_period_id')
                ->selectRaw('SUM(total_units) as total_units')
                ->whereIn('subscription_period_id', $periodIds)
                ->whereDate('usage_date', '>=', $monthStart->toDateString())
                ->whereDate('usage_date', '<', $nextMonthStart->toDateString())
                ->groupBy('subscription_period_id')
                ->pluck('total_units', 'subscription_period_id');
        }

        // ---------------------------------------------------------
        // 4. Calculate projected overage revenue
        // ---------------------------------------------------------
        $projectedOverageRevenue = 0.0;

        foreach ($currentPeriods as $period) {
            $usageUnits = (int) ($usageByPeriod[$period->id] ?? 0);

            $overageUnits = max(
                0,
                $usageUnits - (int) $period->included_units
            );

            $projectedOverageRevenue +=
                $overageUnits * (float) $period->overage_rate;
        }

        // ---------------------------------------------------------
        // 5. Current month usage by customer
        // ---------------------------------------------------------
        $currentUsage = DailyUsage::query()
            ->select('customer_id')
            ->selectRaw('SUM(total_units) as total_units')
            ->where('merchant_id', $merchant->id)
            ->whereDate('usage_date', '>=', $monthStart->toDateString())
            ->whereDate('usage_date', '<', $nextMonthStart->toDateString())
            ->groupBy('customer_id')
            ->pluck('total_units', 'customer_id');

        // ---------------------------------------------------------
        // 6. Previous month usage by customer
        // ---------------------------------------------------------
        $previousUsage = DailyUsage::query()
            ->select('customer_id')
            ->selectRaw('SUM(total_units) as total_units')
            ->where('merchant_id', $merchant->id)
            ->whereDate('usage_date', '>=', $previousMonthStart->toDateString())
            ->whereDate('usage_date', '<', $monthStart->toDateString())
            ->groupBy('customer_id')
            ->pluck('total_units', 'customer_id');

        // ---------------------------------------------------------
        // 7. Customers whose usage dropped by more than 50%
        // ---------------------------------------------------------
        $dropCustomers = $previousUsage
            ->filter(function ($previousUnits, $customerId) use ($currentUsage) {
                if ((int) $previousUnits <= 0) {
                    return false;
                }

                $currentUnits = (int) ($currentUsage[$customerId] ?? 0);

                return $currentUnits < ((int) $previousUnits * 0.5);
            })
            ->keys();

        $customersWithUsageDrop = $merchant->customers()
            ->whereIn('id', $dropCustomers)
            ->get(['id', 'name', 'email'])
            ->map(function ($customer) use ($currentUsage, $previousUsage) {
                $previousUnits = (int) $previousUsage[$customer->id];
                $currentUnits = (int) ($currentUsage[$customer->id] ?? 0);

                $dropPercentage = $previousUnits > 0
                    ? (($previousUnits - $currentUnits) / $previousUnits) * 100
                    : 0;

                return [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'email' => $customer->email,
                    'previous_month_usage' => $previousUnits,
                    'current_month_usage' => $currentUnits,
                    'drop_percentage' => round($dropPercentage, 2),
                ];
            })
            ->values();

        $dashboardData = [
            'merchant_id' => $merchant->id,

            'period' => [
                'current_month_start' => $monthStart->toDateString(),
                'current_month_end' => $nextMonthStart
                    ->copy()
                    ->subDay()
                    ->toDateString(),
            ],

            'top_customers_by_usage' => $topCustomers,

            'projected_overage_revenue' => round(
                $projectedOverageRevenue,
                4
            ),

            'customers_with_usage_drop_over_50_percent' =>
                $customersWithUsageDrop,
        ];

        if ($request->expectsJson()) {
            return response()->json($dashboardData);
        }

        return view(
            'merchants.dashboard',
            $dashboardData
        );
    }
}