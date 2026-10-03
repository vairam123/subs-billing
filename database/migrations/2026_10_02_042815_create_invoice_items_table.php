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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('subscription_period_id')
                ->nullable()
                ->constrained('subscription_periods')
                ->restrictOnDelete();

            $table->string('type', 30);

            $table->string('description');

            $table->unsignedBigInteger('usage_units')->default(0);

            $table->decimal('unit_price', 12, 6)->default(0);

            $table->decimal('quantity', 12, 4)->default(0);

            $table->decimal('amount', 12, 4);

            $table->timestamps();

            $table->index([
                'invoice_id',
                'type',
            ]);

            $table->index('subscription_period_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};