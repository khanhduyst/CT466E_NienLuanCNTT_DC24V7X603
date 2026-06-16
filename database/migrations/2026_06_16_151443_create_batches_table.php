<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('batch_code', 100);
            $table->integer('quantity');
            $table->date('expiration_date');
            $table->timestamps();

            $table->index('expiration_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};