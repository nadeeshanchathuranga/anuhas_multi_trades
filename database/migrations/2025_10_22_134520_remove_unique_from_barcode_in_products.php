<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the unique index exists before trying to drop it
        $indexExists = collect(DB::select("SHOW INDEXES FROM products WHERE Key_name = 'products_barcode_unique'"))->isNotEmpty();
        
        if ($indexExists) {
            Schema::table('products', function (Blueprint $table) {
                // Remove the unique constraint from barcode
                $table->dropUnique(['barcode']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add back the unique constraint if rolling back
            $table->unique('barcode');
        });
    }
};