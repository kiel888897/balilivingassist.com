<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryManagementTables extends Migration
{
    public function up()
    {
        Schema::create('delivery_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('max_weight_label', 500);
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_vehicle_id')->constrained()->cascadeOnDelete();
            $table->decimal('distance_min_km', 7, 2);
            $table->decimal('distance_max_km', 7, 2);
            $table->decimal('fee', 12, 2)->nullable();
            $table->boolean('is_price_on_application')->default(false);
            $table->timestamps();

            $table->unique(
                ['delivery_vehicle_id', 'distance_min_km', 'distance_max_km'],
                'delivery_rate_distance_unique'
            );
            $table->index(['delivery_vehicle_id', 'distance_min_km']);
        });

        Schema::create('delivery_coverage_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedSmallInteger('radius_km')->default(45);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_coverage_areas');
        Schema::dropIfExists('delivery_rates');
        Schema::dropIfExists('delivery_vehicles');
    }
}
