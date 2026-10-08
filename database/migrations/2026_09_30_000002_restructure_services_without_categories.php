<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RestructureServicesWithoutCategories extends Migration
{
    private $serviceItems = [
        'renovation_building' => [
            'General renovation & refurbishment',
            'Painting, finishing & surface works',
            'Carpentry and installation',
            'Plumbing, electrical & MEP coordination',
            'Small construction & improvement works',
        ],
        'service_maintenance' => [
            'Air Conditioner Repair',
            'General property repairs',
            'Electrical troubleshooting',
            'Plumbing & water-related repairs',
            'Preventive maintenance',
            'On-site inspection & service coordination',
        ],
        'procurement_supply' => [
            'Electronic Supplies',
            'Local supplier coordination',
            'Building & renovation materials',
            'Project-specific sourcing',
            'Maintenance supplies',
            'Hardware, fixtures & fittings',
        ],
    ];

    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('service_area')->default('service_maintenance')->after('category_id');
        });

        $legacyServices = DB::table('services')
            ->leftJoin('categories', 'services.category_id', '=', 'categories.id')
            ->select('services.id', 'services.name', 'categories.name as category_name')
            ->get();

        foreach ($legacyServices as $service) {
            $legacyText = strtolower(($service->category_name ?: '') . ' ' . $service->name);
            if (preg_match('/procurement|supply|sourcing|electronic|supplier/', $legacyText)) {
                $area = 'procurement_supply';
            } elseif (preg_match('/renovation|building|construction|carpentry|refurbishment/', $legacyText)) {
                $area = 'renovation_building';
            } else {
                $area = 'service_maintenance';
            }

            DB::table('services')->where('id', $service->id)->update(['service_area' => $area]);
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->index('service_area');
        });

        $now = now();
        foreach ($this->serviceItems as $area => $items) {
            foreach ($items as $name) {
                $slug = Str::slug($name);
                if (DB::table('services')->where('slug', $slug)->exists()) {
                    continue;
                }

                DB::table('services')->insert([
                    'service_area' => $area,
                    'name' => $name,
                    'slug' => $slug,
                    'pricing_type' => 'request',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->dropIndex(['service_area']);
        });

        $areas = [
            'renovation_building' => ['service-renovation-building', 'Renovation & Building'],
            'service_maintenance' => ['service-maintenance', 'Service & Maintenance'],
            'procurement_supply' => ['procurement-supply', 'Procurement & Supply'],
        ];

        foreach ($areas as $area => [$slug, $name]) {
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id');
            if (!$categoryId) {
                $categoryId = DB::table('categories')->insertGetId([
                    'name' => $name,
                    'slug' => $slug,
                    'type' => 'services',
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('services')->where('service_area', $area)->update(['category_id' => $categoryId]);
        }

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('service_area');
        });
    }
}
