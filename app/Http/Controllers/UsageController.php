<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsageRequest;
use App\Models\Customer;
use App\Models\UsageEvent;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use App\Models\SubscriptionPeriod;
use App\Jobs\AggregateDailyUsageJob;

class UsageController extends Controller
{
    /**
    * Record usage for a customer's active subscription period.
    *
    * Enforces merchant/customer isolation and idempotency, persists the usage
    * event, and queues daily aggregation without doing aggregation inline.
    */
    public function store(StoreUsageRequest $request): JsonResponse
    {
        // Confirm the customer must belongs to merchant
        $customer = Customer::query()
                    ->where('id', $request->integer('customer_id'))
                    ->where('merchant_id', $request->integer('merchant_id'))
                    ->firstOrFail();
        // Ensure itempotency that request must be processed 1 time
        $idempotencyKey = $request->string('idempotency_key')->toString();

        /*
         * Check whether this request was already processed.
         */
        $existingEvent = UsageEvent::query()
            ->where('merchant_id', $customer->merchant_id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existingEvent) {
            return $this->handleExistingEvent(
                $existingEvent,
                $request
            );
        }

        try {
            // used to identify subscription period
            $usageDate = $request->date('usage_date');

            $subscriptionPeriod = SubscriptionPeriod::query()
                ->whereHas('subscription', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->where('starts_at', '<=', $usageDate->toDateString())
                ->where('ends_at', '>', $usageDate->toDateString())
                ->orderByDesc('starts_at')
                ->first();
            if (!$subscriptionPeriod) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No subscription period found for the usage date.',
                ], 422);
            }

            $usageEvent = UsageEvent::create([
                'merchant_id' => $customer->merchant_id,
                'customer_id' => $customer->id,
                'subscription_period_id' => $subscriptionPeriod->id,
                'idempotency_key' => $idempotencyKey,
                'usage_date' => $request->date('usage_date'),
                'units' => $request->integer('units'),
            ]);

            // Dispatch job
            AggregateDailyUsageJob::dispatch(
                $customer->id,
                $subscriptionPeriod->id,
                $usageDate->toDateString()
            );
        } catch (QueryException $e) {
            /*
             * Another request may have inserted the same
             * idempotency key between our SELECT and INSERT.
             *
             * The database unique constraint protects us
             * from creating a duplicate event.
             */
            if ($e->getCode() !== '23000') {
                throw $e;
            }

            $usageEvent = UsageEvent::query()
                ->where('merchant_id', $customer->merchant_id)
                ->where('idempotency_key', $idempotencyKey)
                ->firstOrFail();

            return $this->handleExistingEvent(
                $usageEvent,
                $request
            );
        }

        return response()->json([
            'status' => 'ok',
            'usage_event_id' => $usageEvent->id,
        ], 201);
    }

    /**
    * Resolve a repeated usage request using its existing idempotency key.
    *
    * Returns the existing event when the request matches, or a conflict when
    * the same key is reused with different usage data.
    */
    private function handleExistingEvent(
        UsageEvent $existingEvent,
        StoreUsageRequest $request
    ): JsonResponse {
        $sameCustomer =
            $existingEvent->customer_id ===
            $request->integer('customer_id');

        $sameUnits =
            $existingEvent->units ===
            $request->integer('units');

        $sameDate =
            $existingEvent->usage_date->toDateString() ===
            $request->date('usage_date')->toDateString();

        if (
            !$sameCustomer ||
            !$sameUnits ||
            !$sameDate
        ) {
            return response()->json([
                'status' => 'conflict',
                'message' => 'The idempotency key has already been used with a different request.',
            ], 409);
        }

        return response()->json([
            'status' => 'already_processed',
            'usage_event_id' => $existingEvent->id,
        ], 200);
    }
}