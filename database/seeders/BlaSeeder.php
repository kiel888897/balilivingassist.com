<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BlaSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Building Materials', 'slug' => 'building-materials', 'type' => 'shop', 'description' => 'Construction and finishing essentials.'],
            ['name' => 'Electronics', 'slug' => 'electronics', 'type' => 'shop', 'description' => 'Electrical and smart home equipment.'],
            ['name' => 'Furniture', 'slug' => 'furniture', 'type' => 'shop', 'description' => 'Indoor and outdoor furniture.'],
            ['name' => 'Renovation', 'slug' => 'renovation', 'type' => 'services', 'description' => 'Interior and exterior renovation.'],
            ['name' => 'Maintenance', 'slug' => 'maintenance', 'type' => 'services', 'description' => 'Routine upkeep and property services.'],
            ['name' => 'Power Tools', 'slug' => 'power-tools', 'type' => 'rental', 'description' => 'Rental equipment for projects and jobs.'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        $products = [
            ['category_id' => Category::where('slug', 'building-materials')->value('id'), 'name' => 'Cement Premium', 'slug' => 'cement-premium', 'sku' => 'BLA-001', 'description' => 'High-strength cement for construction projects.', 'sale_price' => 250000, 'rental_price' => null, 'for_sale' => true, 'for_rental' => false, 'is_active' => true],
            ['category_id' => Category::where('slug', 'electronics')->value('id'), 'name' => 'Smart Water Pump', 'slug' => 'smart-water-pump', 'sku' => 'BLA-002', 'description' => 'Efficient and reliable water supply solution.', 'sale_price' => 1800000, 'rental_price' => 350000, 'for_sale' => true, 'for_rental' => true, 'is_active' => true],
            ['category_id' => Category::where('slug', 'furniture')->value('id'), 'name' => 'Outdoor Dining Set', 'slug' => 'outdoor-dining-set', 'sku' => 'BLA-003', 'description' => 'Modern furniture set designed for villa outdoor spaces.', 'sale_price' => 4200000, 'rental_price' => 550000, 'for_sale' => true, 'for_rental' => true, 'is_active' => true],
            ['category_id' => Category::where('slug', 'power-tools')->value('id'), 'name' => 'Concrete Mixer', 'slug' => 'concrete-mixer', 'sku' => 'BLA-004', 'description' => 'Portable concrete mixer for renovation jobs.', 'sale_price' => 25000000, 'rental_price' => 350000, 'for_sale' => true, 'for_rental' => true, 'is_active' => true],
            ['category_id' => Category::where('slug', 'renovation')->value('id'), 'name' => 'Villa Renovation Package', 'slug' => 'villa-renovation-package', 'sku' => 'BLA-005', 'description' => 'Turnkey renovation support for villas and residential projects.', 'sale_price' => 15000000, 'rental_price' => null, 'for_sale' => true, 'for_rental' => false, 'is_active' => true],
            ['category_id' => Category::where('slug', 'maintenance')->value('id'), 'name' => 'Property Maintenance', 'slug' => 'property-maintenance', 'sku' => 'BLA-006', 'description' => 'Regular support for cleaning, repair and upkeep.', 'sale_price' => 950000, 'rental_price' => null, 'for_sale' => true, 'for_rental' => false, 'is_active' => true],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(['slug' => $product['slug']], $product);
        }
    }
}
