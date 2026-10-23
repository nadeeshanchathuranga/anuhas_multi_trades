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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('item_discount', 10, 2)->default(0)->after('selling_price')->comment('Manual discount value entered for this line');
            $table->string('item_discount_type', 10)->default('fixed')->after('item_discount');
            $table->decimal('item_discount_amount', 10, 2)->default(0)->after('item_discount_type')->comment('Resulting LKR discount for this line');
        });

        Schema::table('credit_bill_items', function (Blueprint $table) {
            $table->decimal('discount', 5, 2)->default(0)->after('total_price');
            $table->boolean('apply_discount')->default(false)->after('discount');
            $table->decimal('discounted_price', 10, 2)->nullable()->after('apply_discount');
            $table->boolean('include_custom')->default(false)->after('discounted_price');
            $table->decimal('selling_price', 10, 2)->nullable()->after('include_custom');
            $table->decimal('item_discount', 10, 2)->default(0)->after('selling_price');
            $table->string('item_discount_type', 10)->default('fixed')->after('item_discount');
            $table->decimal('item_discount_amount', 10, 2)->default(0)->after('item_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['item_discount', 'item_discount_type', 'item_discount_amount']);
        });

        Schema::table('credit_bill_items', function (Blueprint $table) {
            $table->dropColumn([
                'discount',
                'apply_discount',
                'discounted_price',
                'include_custom',
                'selling_price',
                'item_discount',
                'item_discount_type',
                'item_discount_amount',
            ]);
        });
    }
};
