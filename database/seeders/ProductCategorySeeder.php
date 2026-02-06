<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = \App\Models\Product::all();
        $categories = \App\Models\Category::all();

        if ($categories->isEmpty()) {
            $this->command->info('No categories found. Skipping assignment.');
            return;
        }

        foreach ($products as $product) {
            // Get 1 to 3 random categories
            $randomCategories = $categories->random(rand(1, min(3, $categories->count())));

            // Sync categories (replaces existing associations)
            $product->categories()->sync($randomCategories->pluck('id'));
        }
    }
}
