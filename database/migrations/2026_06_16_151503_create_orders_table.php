<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('customer_id')->nullable()->constrained('customers');
            $table->string('invoice_number', 100)->unique();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('final_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0.00);
            $table->enum('payment_method', ['cash', 'qr_code', 'debt']);
            $table->timestamps();

            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};