<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
                ->constrained('merchants')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->foreignId('subscription_id')
                ->constrained('subscriptions')
                ->restrictOnDelete();

            $table->date('billing_period_start');
            $table->date('billing_period_end');

            $table->decimal('subtotal', 12, 4);
            $table->decimal('total', 12, 4);

            $table->string('currency', 3);

            $table->string('status', 20)->default('draft');

            $table->timestamps();

            /*
             * A subscription should have only one invoice
             * for a particular billing period.
             */
            $table->unique(
                [
                    'subscription_id',
                    'billing_period_start',
                    'billing_period_end',
                ],
                'invoice_period_unique'
            );

            /*
             * Useful for merchant dashboard/reporting.
             */
            $table->index([
                'merchant_id',
                'billing_period_start',
            ]);

            /*
             * Useful for customer invoice history.
             */
            $table->index([
                'customer_id',
                'billing_period_start',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};