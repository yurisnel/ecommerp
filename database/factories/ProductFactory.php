<?php

namespace Database\Factories;

use App\Models\Category;
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
            'category_id' => Category::factory(),
            'barcode' => $this->faker->ean13,
            'unit' => 'pcs',
            'min_stock' => 5,
            'max_stock' => 100,
            'image' => 'https://picsum.photos/400?random=' . $this->faker->numberBetween(1, 1000),
            'status' => 'active',
        ];
    }
}
