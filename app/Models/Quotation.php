<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Quotation extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (!$quotation->public_token) {
                $quotation->public_token = Str::random(64);
            }
        });
    }

    public const ORDER_STATUSES = [
        'payment_requested',
        'payment_review',
        'payment_confirmed',
        'processing',
        'shipped',
        'completed',
        'cancelled',
    ];

    public const STATUSES = [
        'draft',
        'waiting_admin',
        'waiting_customer',
        'accepted',
        'rejected',
        'changes_requested',
        'payment_requested',
        'payment_review',
        'payment_confirmed',
        'processing',
        'shipped',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'quote_number',
        'public_token',
        'user_id',
        'customer_name',
        'customer_phone',
        'shipping_address',
        'customer_note',
        'admin_note',
        'payment_instructions',
        'status',
        'is_order',
        'decision_note',
        'payment_rejection_reason',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total',
        'payment_requested_at',
        'payment_confirmed_at',
    ];

    protected $casts = [
        'is_order' => 'boolean',
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

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(QuotationPaymentProof::class)->latest();
    }

    public function hasPaidInvoice(): bool
    {
        return in_array($this->status, ['payment_confirmed', 'processing', 'shipped', 'completed'], true)
            && $this->paymentProofs()->exists();
    }

    public function isOrder(): bool
    {
        return $this->is_order || in_array($this->status, self::ORDER_STATUSES, true);
    }
}
