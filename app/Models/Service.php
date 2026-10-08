<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function getSanitizedDescriptionAttribute(): string
    {
        return HtmlSanitizer::sanitize($this->description);
    }
}
