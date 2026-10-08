<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProductDetailSchema extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('name');
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_id', 'sort_order']);
        });

        $now = now();
        foreach (DB::table('products')->whereNotNull('image_path')->get(['id', 'image_path']) as $product) {
            DB::table('product_images')->insert([
                'product_id' => $product->id,
                'image_path' => $product->image_path,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('input_type', 20)->default('radio');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'slug']);
            $table->unique(['id', 'product_id']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_attribute_id');
            $table->string('value', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign(['product_attribute_id', 'product_id'], 'product_attribute_values_attribute_product_fk')
                ->references(['id', 'product_id'])
                ->on('product_attributes')
                ->onDelete('cascade');
            $table->unique(['product_attribute_id', 'value']);
            $table->unique(['id', 'product_id', 'product_attribute_id'], 'product_attribute_values_identity_unique');
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('sku', 100)->unique();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['id', 'product_id']);
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedBigInteger('product_attribute_id');
            $table->unsignedBigInteger('product_attribute_value_id');
            $table->timestamps();
            $table->foreign(['product_variant_id', 'product_id'], 'product_variant_values_variant_product_fk')
                ->references(['id', 'product_id'])
                ->on('product_variants')
                ->onDelete('cascade');
            $table->foreign(['product_attribute_id', 'product_id'], 'product_variant_values_attribute_product_fk')
                ->references(['id', 'product_id'])
                ->on('product_attributes')
                ->onDelete('cascade');
            $table->foreign(
                ['product_attribute_value_id', 'product_id', 'product_attribute_id'],
                'product_variant_values_option_fk'
            )
                ->references(['id', 'product_id', 'product_attribute_id'])
                ->on('product_attribute_values')
                ->onDelete('cascade');
            $table->unique(['product_variant_id', 'product_attribute_id'], 'pvv_variant_attribute_unique');
            $table->unique(['product_variant_id', 'product_attribute_value_id'], 'product_variant_values_pair_unique');
        });

        Schema::create('product_variant_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('min_quantity');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();
            $table->unique(['product_variant_id', 'min_quantity'], 'pvpt_variant_min_qty_unique');
        });

        Schema::create('frequently_bought_together', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('related_product_id')->constrained('products')->onDelete('cascade');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['product_id', 'related_product_id'], 'fbt_product_related_unique');
            $table->index(['product_id', 'is_active', 'sort_order'], 'fbt_product_active_order_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('frequently_bought_together');
        Schema::dropIfExists('product_variant_price_tiers');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_images');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('subtitle');
        });
    }
}
