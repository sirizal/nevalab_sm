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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->string('short_description')->nullable();
            $table->foreignId('item_type_id')->nullable()->constrained('item_types')->cascadeOnDelete();
            $table->foreignId('uom_id')->constrained('uoms')->cascadeOnDelete();
            $table->foreignId('storage_type_id')->constrained('storage_types')->cascadeOnDelete();
            $table->foreignId('category_1')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->foreignId('category_2')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->foreignId('category_3')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->foreignId('category_4')->nullable()->constrained('categories')->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->boolean('critical_item')->default(false);
            $table->integer('standard_cost')->default(0);
            $table->integer('grammage')->default(0);
            $table->string('sps_file')->nullable();
            $table->string('brand')->nullable();
            $table->string('barcode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
