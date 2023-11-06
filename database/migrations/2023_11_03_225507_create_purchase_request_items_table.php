<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained();
            $table->foreignId('item_id')->nullable()->constrained();
            $table->string('gl_no')->nullable();
            $table->string('description')->nullable();
            $table->integer('uom_id')->nullable();
            $table->integer('standard_cost')->default(0);
            $table->integer('purchase_uom')->nullable();
            $table->integer('conversion_uom_qty')->default(1);
            $table->integer('purchase_price')->default(0);
            $table->integer('conversion_price')->default(0);
            $table->integer('purchase_price_variance')->default(0);
            $table->decimal('vat', 8, 2)->default(0);
            $table->decimal('request_qty', 24, 2)->default(0);
            $table->decimal('purchase_qty', 24, 2)->default(0);
            $table->decimal('conversion_purchase_qty', 24, 2)->default(0);
            $table->string('variant_code')->nullable();
            $table->decimal('toreceive_qty', 24, 2)->default(0);
            $table->decimal('pass_qc_qty', 24, 2)->default(0);
            $table->decimal('reject_qc_qty', 24, 2)->default(0);
            $table->decimal('received_qty', 24, 2)->default(0);
            $table->decimal('toinvoice_qty', 24, 2)->default(0);
            $table->decimal('invoiced_qty', 24, 2)->default(0);
            $table->decimal('return_qty', 24, 2)->default(0);
            $table->date('production_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('doc_no')->nullable();
            $table->string('packing_description')->nullable();
            $table->string('reject_reason')->nullable();
            $table->string('reason_not_deliver')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
