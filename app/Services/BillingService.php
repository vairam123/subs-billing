<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Subscription;
use App\Models\SubscriptionPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate an invoice for one billing cycle.
     *
     * Billing period is treated as [start, end).
     *
     * Each subscription period is billed using its own
     * pricing snapshot. This is important for mid-cycle
     * upgrades/downgrades.
     */
    public function generateInvoice(
        Subscription $subscription,
        Carbon $billingPeriodStart,
        Carbon $billingPeriodEnd
    ): Invoice {
        if ($billingPeriodEnd->lessThanOrEqualTo($billingPeriodStart)) {
            throw new \InvalidArgumentException(
                'Billing period end must be after billing period start.'
            );
        }

        return DB::transaction(function () use (
            $subscription,
            $billingPeriodStart,
            $billingPeriodEnd
        ) {
            /*
             * Prevent duplicate invoices if this operation is
             * retried by a queue worker.
             */
            $existingInvoice = Invoice::query()
                ->where('subscription_id', $subscription->id)
                ->whereDate('billing_period_start', $billingPeriodStart->toDateString())
                ->whereDate('billing_period_end', $billingPeriodEnd->toDateString())
                ->first();

            if ($existingInvoice) {
                return $existingInvoice->load('items');
            }

            $periods = $subscription->periods()
                ->where('starts_at', '<', $billingPeriodEnd)
                ->where('ends_at', '>', $billingPeriodStart)
                ->orderBy('starts_at')
                ->get();

            if ($periods->isEmpty()) {
                throw new \RuntimeException(
                    'No subscription periods overlap the billing period.'
                );
            }

            $invoice = Invoice::create([
                'merchant_id' => $subscription->customer->merchant_id,
                'customer_id' => $subscription->customer_id,
                'subscription_id' => $subscription->id,
                'billing_period_start' => $billingPeriodStart->toDateString(),
                'billing_period_end' => $billingPeriodEnd->toDateString(),
                'subtotal' => 0,
                'total' => 0,
                'currency' => $periods->first()->plan->currency,
                'status' => 'draft',
            ]);

            $subtotal = 0.0;

            foreach ($periods as $period) {
                $result = $this->calculatePeriodCharges(
                    $period,
                    $billingPeriodStart,
                    $billingPeriodEnd
                );

                if ($result['base_amount'] > 0) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'subscription_period_id' => $period->id,
                        'type' => 'base',
                        'description' => sprintf(
                            '%s subscription charge',
                            $period->plan->name
                        ),
                        'usage_units' => 0,
                        'unit_price' => $period->base_price,
                        'quantity' => $result['proration_fraction'],
                        'amount' => $result['base_amount'],
                    ]);

                    $subtotal += $result['base_amount'];
                }

                if ($result['overage_amount'] > 0) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'subscription_period_id' => $period->id,
                        'type' => 'overage',
                        'description' => sprintf(
                            '%s usage overage',
                            $period->plan->name
                        ),
                        'usage_units' => $result['overage_units'],
                        'unit_price' => $period->overage_rate,
                        'quantity' => $result['overage_units'],
                        'amount' => $result['overage_amount'],
                    ]);

                    $subtotal += $result['overage_amount'];
                }
            }

            $subtotal = round($subtotal, 4);

            $invoice->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'issued',
            ]);

            return $invoice->fresh('items');
        });
    }

    /**
     * Calculate charges for one subscription period.
     */
    public function calculatePeriodCharges(
        SubscriptionPeriod $period,
        Carbon $billingPeriodStart,
        Carbon $billingPeriodEnd
    ): array {
        $periodStart = Carbon::parse($period->starts_at);
        $periodEnd = Carbon::parse($period->ends_at);

        /*
         * Find the actual portion of this subscription period
         * that falls inside the invoice's billing cycle.
         */
        $chargeStart = $periodStart->greaterThan($billingPeriodStart)
            ? $periodStart
            : $billingPeriodStart;

        $chargeEnd = $periodEnd->lessThan($billingPeriodEnd)
            ? $periodEnd
            : $billingPeriodEnd;

        if ($chargeEnd->lessThanOrEqualTo($chargeStart)) {
            return [
                'proration_fraction' => 0,
                'base_amount' => 0,
                'total_units' => 0,
                'included_units' => 0,
                'overage_units' => 0,
                'overage_amount' => 0,
            ];
        }

        /*
         * Proration is based on the exact duration of the
         * subscription segment relative to the billing cycle.
         */
        $billingSeconds = $billingPeriodStart->diffInSeconds(
            $billingPeriodEnd
        );

        $segmentSeconds = $chargeStart->diffInSeconds($chargeEnd);

        $prorationFraction = $segmentSeconds / $billingSeconds;

        $baseAmount = round(
            (float) $period->base_price * $prorationFraction,
            4
        );

        /*
         * Usage belongs to this subscription period, which means
         * usage before/after a plan change cannot be mixed.
         */
        $totalUnits = (int) $period->dailyUsage()
            ->whereDate('usage_date', '>=', $chargeStart->toDateString())
            ->whereDate('usage_date', '<', $chargeEnd->toDateString())
            ->sum('total_units');

        /*
         * Included units are prorated for a partial billing period.
         *
         * Example:
         * 10,000 included units × 50% of cycle = 5,000 included units.
         */
        $includedUnits = (int) floor(
            (int) $period->included_units * $prorationFraction
        );

        $overageUnits = max(
            0,
            $totalUnits - $includedUnits
        );

        $overageAmount = round(
            $overageUnits * (float) $period->overage_rate,
            4
        );

        return [
            'proration_fraction' => round($prorationFraction, 8),
            'base_amount' => $baseAmount,
            'total_units' => $totalUnits,
            'included_units' => $includedUnits,
            'overage_units' => $overageUnits,
            'overage_amount' => $overageAmount,
        ];
    }
}