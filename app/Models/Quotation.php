<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const ORDER_STATUSES = [
        'payment_confirmed',
        'processing',
        'shipped',
        'completed',
    ];

    public const STATUSES = [
        'new',
        'under_review',
        'needs_information',
        'payment_requested',
        'payment_confirmed',
        'processing',
        'shipped',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'quote_number',
        'user_id',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'customer_note',
        'admin_note',
        'payment_instructions',
        'status',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total',
        'payment_requested_at',
        'payment_confirmed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_requested_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(QuotationRevision::class)->with('editor')->latest();
    }

    public function isOrder(): bool
    {
        return in_array($this->status, self::ORDER_STATUSES, true);
    }
}
