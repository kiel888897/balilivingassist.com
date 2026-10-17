<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_area',
        'name',
        'slug',
        'description',
        'pricing_type',
        'price',
        'image_path',
        'is_active',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(ServiceArea::class, 'service_area', 'key');
    }

    public function getSanitizedDescriptionAttribute(): string
    {
        return HtmlSanitizer::sanitize($this->description);
    }
}
