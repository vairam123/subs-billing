<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function periods(): HasMany
    {
        return $this->hasMany(SubscriptionPeriod::class);
    }

    public function activePeriod(): HasOne
    {
        return $this->hasOne(SubscriptionPeriod::class)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->latestOfMany('starts_at');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}