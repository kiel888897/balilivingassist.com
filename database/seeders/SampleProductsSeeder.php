<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'category' => 'building-materials',
                'name' => 'Porcelain Wall & Floor Tile',
                'subtitle' => 'Durable porcelain tile for modern residential spaces',
                'slug' => 'sample-porcelain-wall-floor-tile',
                'sku' => 'BLA-TILE-SAMPLE',
                'description' => 'Low-porosity porcelain tiles suitable for indoor floors, bathrooms and covered outdoor areas. Choose the size and surface finish that best fits your project.',
                'sale_price' => 110000,
                'image' => 'products/samples/porcelain-tile.svg',
                'images' => [
                    ['products/samples/porcelain-tile.svg', 'Porcelain tiles in neutral stone finishes'],
                    ['products/samples/porcelain-tile-detail.svg', 'Close-up of the porcelain tile surface'],
                ],
                'attributes' => [
                    ['name' => 'Tile Size', 'slug' => 'tile-size', 'input_type' => 'select', 'values' => ['30 x 60 cm', '60 x 60 cm']],
                    ['name' => 'Surface Finish', 'slug' => 'surface-finish', 'input_type' => 'radio', 'values' => ['Matte', 'Polished']],
                ],
                'variants' => [
                    ['sku' => 'BLA-TILE-3060-MAT', 'price' => 110000, 'stock' => 85, 'options' => ['tile-size' => '30 x 60 cm', 'surface-finish' => 'Matte'], 'bulk_price' => 104500],
                    ['sku' => 'BLA-TILE-3060-POL', 'price' => 118000, 'stock' => 64, 'options' => ['tile-size' => '30 x 60 cm', 'surface-finish' => 'Polished'], 'bulk_price' => 112100],
                    ['sku' => 'BLA-TILE-6060-MAT', 'price' => 185000, 'stock' => 92, 'options' => ['tile-size' => '60 x 60 cm', 'surface-finish' => 'Matte'], 'bulk_price' => 175750],
                    ['sku' => 'BLA-TILE-6060-POL', 'price' => 198000, 'stock' => 58, 'options' => ['tile-size' => '60 x 60 cm', 'surface-finish' => 'Polished'], 'bulk_price' => 188100],
                ],
            ],
            [
                'category' => 'electronics',
                'name' => 'Smart Wi-Fi Door Lock',
                'subtitle' => 'Keyless access with app control and activity history',
                'slug' => 'sample-smart-wifi-door-lock',
                'sku' => 'BLA-LOCK-SAMPLE',
                'description' => 'A connected door lock for homes and rental properties. Manage access from a compatible mobile app and choose a keypad or fingerprint model. Includes emergency key access and installation guide.',
                'sale_price' => 1890000,
                'image' => 'products/samples/smart-door-lock.svg',
                'images' => [
                    ['products/samples/smart-door-lock.svg', 'Smart door lock installed on a wooden door'],
                    ['products/samples/smart-door-lock-detail.svg', 'Smart lock keypad and fingerprint reader detail'],
                ],
                'attributes' => [
                    ['name' => 'Lock Finish', 'slug' => 'lock-finish', 'input_type' => 'radio', 'values' => ['Matte Black', 'Brushed Silver']],
                    ['name' => 'Access Method', 'slug' => 'access-method', 'input_type' => 'select', 'values' => ['PIN + RFID', 'PIN + Fingerprint']],
                ],
                'variants' => [
                    ['sku' => 'BLA-LOCK-BLK-RFID', 'price' => 1890000, 'stock' => 18, 'options' => ['lock-finish' => 'Matte Black', 'access-method' => 'PIN + RFID'], 'bulk_price' => 1795500],
                    ['sku' => 'BLA-LOCK-BLK-FP', 'price' => 2190000, 'stock' => 12, 'options' => ['lock-finish' => 'Matte Black', 'access-method' => 'PIN + Fingerprint'], 'bulk_price' => 2080500],
                    ['sku' => 'BLA-LOCK-SLV-RFID', 'price' => 1950000, 'stock' => 15, 'options' => ['lock-finish' => 'Brushed Silver', 'access-method' => 'PIN + RFID'], 'bulk_price' => 1852500],
                    ['sku' => 'BLA-LOCK-SLV-FP', 'price' => 2250000, 'stock' => 9, 'options' => ['lock-finish' => 'Brushed Silver', 'access-method' => 'PIN + Fingerprint'], 'bulk_price' => 2137500],
                ],
            ],
            [
                'category' => 'furniture',
                'name' => 'Teak Outdoor Lounge Chair',
                'subtitle' => 'Solid teak seating made for relaxed outdoor living',
                'slug' => 'sample-teak-outdoor-lounge-chair',
                'sku' => 'BLA-CHAIR-SAMPLE',
                'description' => 'A sturdy lounge chair crafted from kiln-dried teak for patios, gardens and villa terraces. Select a wood grade and optional weather-resistant cushion.',
                'sale_price' => 2850000,
                'image' => 'products/samples/teak-lounge-chair.svg',
                'images' => [
                    ['products/samples/teak-lounge-chair.svg', 'Teak lounge chair with outdoor cushion'],
                    ['products/samples/teak-lounge-chair-detail.svg', 'Detail of the teak frame and woven cushion'],
                ],
                'attributes' => [
                    ['name' => 'Teak Grade', 'slug' => 'teak-grade', 'input_type' => 'radio', 'values' => ['Grade A', 'Grade B']],
                    ['name' => 'Cushion', 'slug' => 'cushion', 'input_type' => 'select', 'values' => ['Without Cushion', 'Beige Outdoor Cushion']],
                ],
                'variants' => [
                    ['sku' => 'BLA-CHAIR-A-BARE', 'price' => 2850000, 'stock' => 8, 'options' => ['teak-grade' => 'Grade A', 'cushion' => 'Without Cushion'], 'bulk_price' => 2707500],
                    ['sku' => 'BLA-CHAIR-A-CUSH', 'price' => 3250000, 'stock' => 6, 'options' => ['teak-grade' => 'Grade A', 'cushion' => 'Beige Outdoor Cushion'], 'bulk_price' => 3087500],
                    ['sku' => 'BLA-CHAIR-B-BARE', 'price' => 2350000, 'stock' => 11, 'options' => ['teak-grade' => 'Grade B', 'cushion' => 'Without Cushion'], 'bulk_price' => 2232500],
                    ['sku' => 'BLA-CHAIR-B-CUSH', 'price' => 2750000, 'stock' => 7, 'options' => ['teak-grade' => 'Grade B', 'cushion' => 'Beige Outdoor Cushion'], 'bulk_price' => 2612500],
                ],
            ],
        ];

        DB::transaction(function () use ($products): void {
            $seededProducts = [];

            foreach ($products as $productData) {
                $category = Category::where('slug', $productData['category'])
                    ->where('type', 'shop')
                    ->firstOrFail();

                $product = Product::updateOrCreate(
                    ['slug' => $productData['slug']],
                    [
                        'category_id' => $category->id,
                        'name' => $productData['name'],
                        'subtitle' => $productData['subtitle'],
                        'sku' => $productData['sku'],
                        'description' => $productData['description'],
                        'image_path' => $productData['image'],
                        'sale_price' => $productData['sale_price'],
                        'rental_price' => null,
                        'for_sale' => true,
                        'for_rental' => false,
                        'is_active' => true,
                    ]
                );

                foreach ($productData['images'] as $sortOrder => $image) {
                    ProductImage::updateOrCreate(
                        ['product_id' => $product->id, 'sort_order' => $sortOrder],
                        ['image_path' => $image[0], 'alt_text' => $image[1]]
                    );
                }

                $attributeValues = [];
                foreach ($productData['attributes'] as $sortOrder => $attributeData) {
                    $attribute = ProductAttribute::updateOrCreate(
                        ['product_id' => $product->id, 'slug' => $attributeData['slug']],
                        [
                            'name' => $attributeData['name'],
                            'input_type' => $attributeData['input_type'],
                            'sort_order' => $sortOrder,
                            'is_active' => true,
                        ]
                    );

                    foreach ($attributeData['values'] as $valueOrder => $value) {
                        $attributeValues[$attributeData['slug']][$value] = ProductAttributeValue::updateOrCreate(
                            ['product_attribute_id' => $attribute->id, 'value' => $value],
                            [
                                'product_id' => $product->id,
                                'sort_order' => $valueOrder,
                            ]
                        );
                    }
                }

                foreach ($productData['variants'] as $variantData) {
                    $variant = ProductVariant::updateOrCreate(
                        ['sku' => $variantData['sku']],
                        [
                            'product_id' => $product->id,
                            'price' => $variantData['price'],
                            'stock_quantity' => $variantData['stock'],
                            'is_active' => true,
                        ]
                    );

                    DB::table('product_variant_values')
                        ->where('product_variant_id', $variant->id)
                        ->delete();

                    foreach ($variantData['options'] as $attributeSlug => $value) {
                        $option = $attributeValues[$attributeSlug][$value];
                        DB::table('product_variant_values')->insert([
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'product_attribute_id' => $option->product_attribute_id,
                            'product_attribute_value_id' => $option->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $variant->priceTiers()->updateOrCreate(
                        ['min_quantity' => 10],
                        ['unit_price' => $variantData['bulk_price']]
                    );
                }

                $seededProducts[] = $product;
            }

            foreach ($seededProducts as $product) {
                foreach ($seededProducts as $relatedProduct) {
                    if ($product->is($relatedProduct)) {
                        continue;
                    }

                    DB::table('frequently_bought_together')->updateOrInsert(
                        [
                            'product_id' => $product->id,
                            'related_product_id' => $relatedProduct->id,
                        ],
                        [
                            'sort_order' => $relatedProduct->id,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        });
    }
}
