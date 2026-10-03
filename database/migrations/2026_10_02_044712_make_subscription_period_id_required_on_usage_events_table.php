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
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('usage_events', function (Blueprint $table) {
            $table->foreignId('subscription_period_id')
                ->nullable()
                ->change();
        });
    }
};