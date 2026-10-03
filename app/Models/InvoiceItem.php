<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'subscription_period_id',
        'type',
        'description',
        'usage_units',
        'unit_price',
        'quantity',
        'amount',
    ];

    protected $casts = [
        'usage_units' => 'integer',
        'unit_price' => 'decimal:6',
        'quantity' => 'decimal:4',
        'amount' => 'decimal:4',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscriptionPeriod(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPeriod::class);
    }
}