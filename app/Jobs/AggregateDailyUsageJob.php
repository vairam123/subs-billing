<?php

namespace App\Jobs;

use App\Models\DailyUsage;
use App\Models\UsageEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class AggregateDailyUsageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $customerId,
        public int $subscriptionPeriodId,
        public string $usageDate
    ) {
    }

    public function handle(): void
    {
        $totalUnits = UsageEvent::query()
            ->where('customer_id', $this->customerId)
            ->where('subscription_period_id', $this->subscriptionPeriodId)
            ->whereDate('usage_date', $this->usageDate)
            ->sum('units');

        $merchantId = UsageEvent::query()
            ->where('customer_id', $this->customerId)
            ->where('subscription_period_id', $this->subscriptionPeriodId)
            ->whereDate('usage_date', $this->usageDate)
            ->value('merchant_id');

        if (!$merchantId) {
            return;
        }

        DailyUsage::updateOrCreate(
            [
                'customer_id' => $this->customerId,
                'subscription_period_id' => $this->subscriptionPeriodId,
                'usage_date' => $this->usageDate,
            ],
            [
                'merchant_id' => $merchantId,
                'total_units' => $totalUnits,
            ]
        );
    }
}