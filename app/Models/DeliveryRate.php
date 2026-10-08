<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRate extends Model
{
    protected $fillable = [
        'distance_min_km',
        'distance_max_km',
        'fee',
        'is_price_on_application',
    ];

    protected $casts = [
        'distance_min_km' => 'decimal:2',
        'distance_max_km' => 'decimal:2',
        'fee' => 'decimal:2',
        'is_price_on_application' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(DeliveryVehicle::class, 'delivery_vehicle_id');
    }
}
