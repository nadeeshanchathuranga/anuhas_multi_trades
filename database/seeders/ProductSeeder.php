<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some categories to associate with products
        $electronics = Category::where('name', 'Electronics')->first();
        $mobilePhones = Category::where('name', 'Mobile Phones')->first();
        $laptops = Category::where('name', 'Laptops')->first();
        $clothing = Category::where('name', 'Clothing')->first();

        $products = [
            [
                'category_id' => $mobilePhones?->id ?? $electronics?->id ?? 1,
                'supplier_id' => null,
                'name' => 'iPhone 15 Pro',
                'size_id' => null,
                'color_id' => null,
                'cost_price' => 900.00,
                'selling_price' => 1199.00,
                'stock_quantity' => 50,
                'barcode' => '123456789012',
                'image' => null,
            ],
            [
                'category_id' => $mobilePhones?->id ?? $electronics?->id ?? 1,
                'supplier_id' => null,
                'name' => 'Samsung Galaxy S24',
                'size_id' => null,
                'color_id' => null,
                'cost_price' => 750.00,
                'selling_price' => 999.00,
                'stock_quantity' => 30,
                'barcode' => '123456789013',
                'image' => null,
            ],
            [
                'category_id' => $laptops?->id ?? $electronics?->id ?? 1,
                'supplier_id' => null,
                'name' => 'MacBook Pro 14"',
                'size_id' => null,
                'color_id' => null,
                'cost_price' => 1800.00,
                'selling_price' => 2199.00,
                'stock_quantity' => 20,
                'barcode' => '123456789014',
                'image' => null,
            ],
            [
                'category_id' => $laptops?->id ?? $electronics?->id ?? 1,
                'supplier_id' => null,
                'name' => 'Dell XPS 15',
                'size_id' => null,
                'color_id' => null,
                'cost_price' => 1200.00,
                'selling_price' => 1499.00,
                'stock_quantity' => 15,
                'barcode' => '123456789015',
                'image' => null,
            ],
            [
                'category_id' => $clothing?->id ?? 2,
                'supplier_id' => null,
                'name' => 'Cotton T-Shirt',
                'size_id' => null,
                'color_id' => null,
                'cost_price' => 10.00,
                'selling_price' => 19.99,
                'stock_quantity' => 100,
                'barcode' => '123456789016',
                'image' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['barcode' => $product['barcode']],
                $product
            );
        }
    }
}
