<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'parent_id' => null,
            ],
            [
                'name' => 'Clothing',
                'parent_id' => null,
            ],
            [
                'name' => 'Food & Beverages',
                'parent_id' => null,
            ],
            [
                'name' => 'Home & Garden',
                'parent_id' => null,
            ],
            [
                'name' => 'Sports & Outdoors',
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        // Add subcategories
        $electronics = Category::where('name', 'Electronics')->first();
        if ($electronics) {
            $subcategories = [
                ['name' => 'Mobile Phones', 'parent_id' => $electronics->id],
                ['name' => 'Laptops', 'parent_id' => $electronics->id],
                ['name' => 'Tablets', 'parent_id' => $electronics->id],
                ['name' => 'Accessories', 'parent_id' => $electronics->id],
            ];

            foreach ($subcategories as $subcategory) {
                Category::updateOrCreate(
                    ['name' => $subcategory['name'], 'parent_id' => $subcategory['parent_id']],
                    $subcategory
                );
            }
        }

        $clothing = Category::where('name', 'Clothing')->first();
        if ($clothing) {
            $subcategories = [
                ['name' => 'Men\'s Wear', 'parent_id' => $clothing->id],
                ['name' => 'Women\'s Wear', 'parent_id' => $clothing->id],
                ['name' => 'Kids Wear', 'parent_id' => $clothing->id],
            ];

            foreach ($subcategories as $subcategory) {
                Category::updateOrCreate(
                    ['name' => $subcategory['name'], 'parent_id' => $subcategory['parent_id']],
                    $subcategory
                );
            }
        }
    }
}
