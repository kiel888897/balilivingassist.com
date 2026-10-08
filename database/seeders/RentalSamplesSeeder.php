<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class RentalSamplesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tile Cutting Tools',
                'slug' => 'tile-cutting-tools',
                'type' => 'rental',
                'description' => 'Tools for cutting and shaping tile and stone.',
                'is_active' => true,
            ],
            [
                'name' => 'Drilling Tools',
                'slug' => 'drilling-tools',
                'type' => 'rental',
                'description' => 'Drills and accessories for renovation work.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        $products = [
            [
                'category' => 'tile-cutting-tools',
                'name' => 'Ceramic Cutter',
                'subtitle' => 'Compact circular saw for tile and ceramic cutting',
                'slug' => 'sample-ceramic-cutter',
                'sku' => 'BLA-RENT-CUT-001',
                'description' => 'A compact corded cutter suitable for ceramic tile and light renovation work. Confirm availability and delivery arrangements with our team before the rental date.',
                'specifications' => "Model | Makita M4100\nContinuous rating input | 1200 W\nBlade diameter | 110 mm\nMaximum cutting capacity | 32 mm\nNo load speed | 13,000 RPM\nDimensions (L × W × H) | 227 × 209 × 166 mm\nNet weight | 2.9 kg\nPower supply cord length | 2.0 m",
                'image' => 'products/rental-samples/ceramic-cutter.svg',
                'alt_text' => 'Illustration of a compact ceramic cutter saw',
                'rental_price' => 60000,
                'weekly_rental_price' => 350000,
                'monthly_rental_price' => 1200000,
            ],
            [
                'category' => 'power-tools',
                'name' => 'Angle Grinder 4 Inch',
                'subtitle' => 'Lightweight grinder for cutting and surface preparation',
                'slug' => 'sample-angle-grinder-4-inch',
                'sku' => 'BLA-RENT-GRD-001',
                'description' => 'A sample 4-inch angle grinder for metal cutting, tile edge work and surface preparation. Confirm the correct disc and intended use with our team.',
                'specifications' => "Model | BLA Sample AG-100\nRated input | 750 W\nDisc diameter | 100 mm\nNo load speed | 11,000 RPM\nSpindle thread | M10\nNet weight | 1.8 kg\nPower supply | 220 V",
                'image' => 'products/rental-samples/angle-grinder.svg',
                'alt_text' => 'Illustration of a handheld angle grinder',
                'rental_price' => 50000,
                'weekly_rental_price' => 280000,
                'monthly_rental_price' => 950000,
            ],
            [
                'category' => 'drilling-tools',
                'name' => 'Rotary Hammer Drill',
                'subtitle' => 'SDS-plus hammer drill for concrete and masonry',
                'slug' => 'sample-rotary-hammer-drill',
                'sku' => 'BLA-RENT-DRL-001',
                'description' => 'A sample rotary hammer drill for drilling into concrete and masonry during repair or installation work. Suitable accessories and availability can be confirmed with our team.',
                'specifications' => "Model | BLA Sample RH-26\nRated input | 800 W\nChuck type | SDS-plus\nImpact energy | 2.7 J\nNo load speed | 0–1,100 RPM\nImpact rate | 0–4,000 BPM\nNet weight | 2.8 kg\nPower supply | 220 V",
                'image' => 'products/rental-samples/rotary-hammer-drill.svg',
                'alt_text' => 'Illustration of an SDS-plus rotary hammer drill',
                'rental_price' => 100000,
                'weekly_rental_price' => 550000,
                'monthly_rental_price' => 1800000,
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])
                ->where('type', 'rental')
                ->firstOrFail();
            $product = Product::firstOrCreate(
                ['slug' => $productData['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'subtitle' => $productData['subtitle'],
                    'sku' => $productData['sku'],
                    'description' => $productData['description'],
                    'specifications' => $productData['specifications'],
                    'image_path' => $productData['image'],
                    'sale_price' => null,
                    'rental_price' => $productData['rental_price'],
                    'weekly_rental_price' => $productData['weekly_rental_price'],
                    'monthly_rental_price' => $productData['monthly_rental_price'],
                    'for_sale' => false,
                    'for_rental' => true,
                    'is_active' => true,
                ]
            );

            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'image_path' => $productData['image'],
                    'alt_text' => $productData['alt_text'],
                ]
            );
        }
    }
}
