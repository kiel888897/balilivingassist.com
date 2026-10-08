<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioProject extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
        'is_sample',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sample' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
