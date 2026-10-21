<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastUsedAtToCustomerAddresses extends Migration
{
    public function up()
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->timestamp('last_used_at')->nullable()->after('address');
        });
    }

    public function down()
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropColumn('last_used_at');
        });
    }
}
