<?php

namespace Database\Seeders;

use App\Models\DeliveryCoverageArea;
use App\Models\DeliveryRate;
use App\Models\DeliveryVehicle;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    public function run()
    {
        $vehicleRates = [
            [
                'name' => 'Motorbike',
                'max_weight_label' => 'Max Weight: 20 kg',
                'sort_order' => 1,
                'last_distance_km' => 45,
                'rates' => [30000, 50000, 70000, 90000, 110000, 130000, 150000, 170000],
            ],
            [
                'name' => 'Pick-up',
                'max_weight_label' => 'Max Weight: 1 tonne',
                'sort_order' => 2,
                'last_distance_km' => 45,
                'rates' => [260000, 300000, 340000, 380000, 400000, 450000, 500000, 550000],
            ],
            [
                'name' => 'Truck',
                'max_weight_label' => "4 Wheel - Max Weight: 2 - 3 tonne\n6 Wheel - Max Weight: 5 - 6 tonne",
                'sort_order' => 3,
                'last_distance_km' => 35,
                'rates' => [null, null, null, null, null, null, null],
            ],
        ];

        foreach ($vehicleRates as $vehicleData) {
            $rates = $vehicleData['rates'];
            $lastDistance = $vehicleData['last_distance_km'];
            unset($vehicleData['rates']);
            unset($vehicleData['last_distance_km']);

            $vehicle = DeliveryVehicle::updateOrCreate(
                ['name' => $vehicleData['name']],
                array_merge($vehicleData, ['is_active' => true])
            );

            foreach ($rates as $index => $fee) {
                $minimum = $index * 5;
                $maximum = $index === count($rates) - 1 ? $lastDistance : $minimum + 5;
                if ($maximum !== $minimum + 5) {
                    DeliveryRate::where('delivery_vehicle_id', $vehicle->id)
                        ->where('distance_min_km', $minimum)
                        ->where('distance_max_km', $minimum + 5)
                        ->update(['distance_max_km' => $maximum]);
                }

                DeliveryRate::updateOrCreate(
                    [
                        'delivery_vehicle_id' => $vehicle->id,
                        'distance_min_km' => $minimum,
                        'distance_max_km' => $maximum,
                    ],
                    [
                        'fee' => $fee,
                        'is_price_on_application' => $fee === null,
                    ]
                );
            }
        }

        DeliveryCoverageArea::updateOrCreate(
            ['name' => 'Bali Living Assist — Denpasar'],
            [
                'latitude' => -8.6705,
                'longitude' => 115.2126,
                'radius_km' => 45,
                'sort_order' => 1,
                'is_active' => true,
            ]
        );
    }
}
