<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('merchant_id')
                ->constrained('merchants')
                ->cascadeOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();

            $table->string('idempotency_key', 100);

            $table->date('usage_date');

            $table->unsignedBigInteger('units');

            $table->timestamps();

            // Prevent the same usage request from being recorded twice.
            $table->unique([
                'merchant_id',
                'idempotency_key',
            ]);

            // Customer daily usage aggregation.
            $table->index([
                'customer_id',
                'usage_date',
            ]);

            // Merchant dashboard/reporting.
            $table->index([
                'merchant_id',
                'usage_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};