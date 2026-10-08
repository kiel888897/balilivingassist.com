<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'sale_price' => 'decimal:2',
        'rental_price' => 'decimal:2',
        'weekly_rental_price' => 'decimal:2',
        'monthly_rental_price' => 'decimal:2',
        'for_sale' => 'boolean',
        'for_rental' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'subtitle',
        'slug',
        'sku',
        'description',
        'specifications',
        'image_path',
        'sale_price',
        'rental_price',
        'weekly_rental_price',
        'monthly_rental_price',
        'for_sale',
        'for_rental',
        'is_active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function rentalPriceForPeriod(string $period): ?float
    {
        $price = [
            'daily' => $this->rental_price,
            'weekly' => $this->weekly_rental_price,
            'monthly' => $this->monthly_rental_price,
        ][$period] ?? null;

        return $price === null ? null : (float) $price;
    }

    public function getPrimaryImagePathAttribute(): ?string
    {
        return $this->images->first()->image_path ?? $this->image_path;
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('id');
    }

    public function frequentlyBoughtTogether()
    {
        return $this->belongsToMany(
            self::class,
            'frequently_bought_together',
            'product_id',
            'related_product_id'
        )
            ->withPivot(['sort_order', 'is_active'])
            ->withTimestamps()
            ->wherePivot('is_active', true)
            ->where('products.is_active', true)
            ->orderBy('frequently_bought_together.sort_order');
    }

    public function getSanitizedDescriptionAttribute(): string
    {
        return HtmlSanitizer::sanitize($this->description);
    }

}
