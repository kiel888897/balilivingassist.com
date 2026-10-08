<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryVehicle extends Model
{
    protected $fillable = [
        'name',
        'max_weight_label',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(DeliveryRate::class)->orderBy('distance_min_km');
    }
}
