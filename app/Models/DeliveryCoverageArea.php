<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryCoverageArea extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_km',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_km' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
