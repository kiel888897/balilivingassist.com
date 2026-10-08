<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPriceTier extends Model
{
    protected $fillable = [
        'product_variant_id',
        'min_quantity',
        'unit_price',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
