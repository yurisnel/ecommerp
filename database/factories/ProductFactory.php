<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\InventoryService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'sku' => strtoupper(Str::random(3) . '-' . $this->faker->unique()->numberBetween(1000, 9999)),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'description' => $this->faker->paragraph,
            'barcode' => $this->faker->ean13,
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'liter', 'meter', 'box']),
            'min_stock' => 5,
            'max_stock' => 100,
            'status' => 'active',
        ];
    }
    /**
     * Configure the model factory
     */
    public function configure()
    {
        $categories = \App\Models\Category::all();
        $inventoryService = new InventoryService();
        $warehouses = \App\Models\Warehouse::all();
        $suppliers = \App\Models\Supplier::all();

        return $this->afterCreating(function ($product) use ($categories, $inventoryService, $warehouses, $suppliers) {
            // Add Categories
            if ($categories->isNotEmpty()) {
                // Get 1 to 3 random categories
                $randomCategories = $categories->random(rand(1, min(3, $categories->count())));

                // Sync categories (replaces existing associations)
                $product->categories()->sync($randomCategories->pluck('id'));
            }

            // Crear 1-2 imágenes para cada producto
            $imageCount = $this->faker->numberBetween(1, 2);

            for ($i = 0; $i < $imageCount; $i++) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'url' => 'https://via.placeholder.com/400x400?text=' . urlencode($product->name),
                    'is_default' => $i === 0, // Primera imagen como predeterminada
                    'sort_order' => $i,
                ]);
            }

            // Create product entries using InventoryService to properly manage inventory
            if ($warehouses->isNotEmpty() && $suppliers->isNotEmpty()) {
                try {
                    $qty = rand(10, 20);
                    $base_cost = rand(5, 10);
                    $inc = $this->faker->randomFloat(1, 1.5, 2);
                    $price = $base_cost * $inc;

                    $inventoryService->createProductEntry([
                        'product_id' => $product->id,
                        //'warehouse_id' => $warehouses->random()->id,
                        'warehouse_id' => 1,
                        'supplier_id' => $suppliers->random()->id,
                        'quantity' =>  $qty,
                        //'unit_cost' => $base_cost,
                        'base_cost' => $base_cost,
                        'unit_price' => $price,
                        'entry_date' => now(),
                        'batch_number' => 'BATCH-' . strtoupper(\Illuminate\Support\Str::random(8)),
                        'notes' => 'Initial inventory',
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error creating product entry: ' . $e->getMessage());
                }
            }
        });
    }
}
