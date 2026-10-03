<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'name',
        'base_price',
        'currency',
        'billing_cycle',
        'included_units',
        'overage_rate',
    ];

    protected $casts = [
        'base_price' => 'decimal:4',
        'included_units' => 'integer',
        'overage_rate' => 'decimal:6',
    ];

    /**
    * It's automatically called whenever
    * the plan model initiated
    * Registers a listener for the Eloquent saved event
    * Whenever plan created/updated cache cleared
    */
    protected static function booted(): void
    {
        static::saved(function (Plan $plan) {
            Cache::forget(
                "merchant:{$plan->merchant_id}:plan:{$plan->id}:pricing"
            );
        });

        static::deleted(function (Plan $plan) {
            Cache::forget(
                "merchant:{$plan->merchant_id}:plan:{$plan->id}:pricing"
            );
        });
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}