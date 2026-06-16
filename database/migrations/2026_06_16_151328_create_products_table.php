<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->string('barcode', 50)->unique();
            $table->string('name');
            $table->integer('minimum_stock_level')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};