<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number', 15)->unique();
            $table->string('name');
            $table->string('barcode', 50)->nullable()->unique();
            $table->integer('current_points')->default(0);
            $table->decimal('total_debt', 15, 2)->default(0.00);
            $table->timestamps();

            $table->index('phone_number');
            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};