<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('name_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['renovation_building', 'Renovation & Building', 'Renovasi & Pembangunan'],
            ['service_maintenance', 'Service & Maintenance', 'Layanan & Pemeliharaan'],
            ['procurement_supply', 'Procurement & Supply', 'Pengadaan & Pasokan'],
        ];

        foreach ($defaults as $index => [$key, $name, $nameId]) {
            DB::table('service_areas')->insert([
                'key' => $key,
                'name' => $name,
                'name_id' => $nameId,
                'sort_order' => $index + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
