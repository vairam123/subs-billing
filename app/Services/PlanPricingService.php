<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Facades\Cache;

class PlanPricingService
{
    private const CACHE_TTL = 3600;

    public function getPlan(int $merchantId, int $planId): Plan
    {
        return Cache::remember(
            $this->cacheKey($merchantId, $planId),
            self::CACHE_TTL,
            function () use ($merchantId, $planId) {
                return Plan::query()
                    ->where('merchant_id', $merchantId)
                    ->whereKey($planId)
                    ->firstOrFail();
            }
        );
    }

    public function forgetPlan(int $merchantId, int $planId): void
    {
        Cache::forget(
            $this->cacheKey($merchantId, $planId)
        );
    }

    private function cacheKey(int $merchantId, int $planId): string
    {
        return "merchant:{$merchantId}:plan:{$planId}:pricing";
    }
}