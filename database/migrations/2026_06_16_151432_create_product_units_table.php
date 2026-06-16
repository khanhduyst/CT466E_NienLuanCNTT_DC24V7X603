<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('unit_name', 50);
            $table->integer('conversion_factor')->default(1);
            $table->decimal('cost_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_base_unit')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};