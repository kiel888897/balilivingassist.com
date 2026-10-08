<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_variant_values',
            'product_variant_id',
            'product_attribute_value_id'
        )->withPivot(['product_id', 'product_attribute_id'])->withTimestamps();
    }

    public function priceTiers()
    {
        return $this->hasMany(ProductVariantPriceTier::class)->orderBy('min_quantity');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    public function unitPriceForQuantity(int $quantity): string
    {
        $tier = $this->priceTiers
            ->where('min_quantity', '<=', $quantity)
            ->sortByDesc('min_quantity')
            ->first();

        return $tier ? $tier->unit_price : $this->price;
    }
}
