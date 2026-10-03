<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usage_events', function (Blueprint $table) {
            $table->foreignId('subscription_period_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('subscription_periods')
                ->restrictOnDelete();

            $table->index([
                'subscription_period_id',
                'usage_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('usage_events', function (Blueprint $table) {
            $table->dropForeign([
                'subscription_period_id',
            ]);

            $table->dropIndex([
                'usage_events_subscription_period_id_usage_date_index',
            ]);

            $table->dropColumn('subscription_period_id');
        });
    }
};