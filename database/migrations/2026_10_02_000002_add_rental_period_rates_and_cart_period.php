<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRentalPeriodRatesAndCartPeriod extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weekly_rental_price', 12, 2)->nullable()->after('rental_price');
            $table->decimal('monthly_rental_price', 12, 2)->nullable()->after('weekly_rental_price');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('rental_period', 16)->default('')->after('variant_key');
            $table->dropUnique('cart_user_product_variant_unique');
            $table->unique(
                ['user_id', 'product_id', 'variant_key', 'rental_period'],
                'cart_user_product_variant_period_unique'
            );
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_user_product_variant_period_unique');
            $table->dropColumn('rental_period');
            $table->unique(
                ['user_id', 'product_id', 'variant_key'],
                'cart_user_product_variant_unique'
            );
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weekly_rental_price', 'monthly_rental_price']);
        });
    }
}
