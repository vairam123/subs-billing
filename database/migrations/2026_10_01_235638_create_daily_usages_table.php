<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_usage', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
                ->constrained('merchants')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('subscription_period_id')
                ->constrained('subscription_periods')
                ->restrictOnDelete();

            $table->date('usage_date');

            $table->unsignedBigInteger('total_units');

            $table->timestamps();

            /*
             * One customer can have multiple billing periods
             * on the same day, but only one summary per period.
             */
            $table->unique([
                'customer_id',
                'subscription_period_id',
                'usage_date',
            ]);

            /*
             * Useful for merchant dashboard/reporting.
             */
            $table->index([
                'merchant_id',
                'usage_date',
            ]);

            /*
             * Useful for customer billing queries.
             */
            $table->index([
                'customer_id',
                'usage_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_usage');
    }
};