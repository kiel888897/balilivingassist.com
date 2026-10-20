<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddPublicPreviewAndPaymentProofsToQuotations extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('quote_number');
        });

        DB::table('quotations')->whereNull('public_token')->orderBy('id')->chunkById(100, function ($quotations) {
            foreach ($quotations as $quotation) {
                DB::table('quotations')->where('id', $quotation->id)->update([
                    'public_token' => Str::random(64),
                ]);
            }
        });

        Schema::create('quotation_payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source', 20);
            $table->string('path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->timestamps();

            $table->index(['quotation_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quotation_payment_proofs');
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
}
