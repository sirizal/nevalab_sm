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
        Schema::create('tally_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests', 'id');
            $table->foreignId('purchase_request_item_id')->constrained('purchase_request_items', 'id');
            $table->integer('pack_qty');
            $table->foreignId('pack_uom')->constrained('uoms', 'id');
            $table->integer('qty_per_pack');
            $table->integer('total_qty');
            $table->foreignId('qty_uom')->constrained('uoms', 'id');
            $table->date('production_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tally_sheets');
    }
};
