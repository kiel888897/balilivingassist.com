<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWorkflowFieldsToQuotations extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('is_order')->default(false)->after('status');
            $table->text('decision_note')->nullable()->after('admin_note');
            $table->text('payment_rejection_reason')->nullable()->after('payment_instructions');
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['is_order', 'decision_note', 'payment_rejection_reason']);
        });
    }
}
