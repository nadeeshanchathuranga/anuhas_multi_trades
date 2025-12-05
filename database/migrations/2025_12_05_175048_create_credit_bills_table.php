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
        Schema::create('credit_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_id');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'card', 'cheque', 'credit'])->default('cash');
            $table->date('sale_date');
            $table->decimal('cash', 10, 2)->default(0);
            $table->boolean('is_whole')->default(false);
            $table->string('custom_discount_type')->nullable();
            $table->decimal('custom_discount', 10, 2)->default(0);
            $table->foreignId('cheque_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_bills');
    }
};
