<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'variant_key',
        'variant_id',
        'rental_period',
        'quantity',
        'is_selected',
    ];

    protected $casts = [
        'variant_key' => 'integer',
        'variant_id' => 'integer',
        'rental_period' => 'string',
        'quantity' => 'integer',
        'is_selected' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
