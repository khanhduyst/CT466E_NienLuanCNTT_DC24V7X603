<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->enum('change_type', ['earn', 'redeem', 'refund']);
            $table->integer('points');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_logs');
    }
};