<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => $this->faker->unique()->bothify('SKU-####-????'),
            'name' => $this->faker->words(3, true),
            'barcode' => $this->faker->unique()->ean13(),
            'weight' => $this->faker->randomFloat(2, 0.1, 5),
            'image_url' => null,
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Create a variant for a specific product with attribute values
     */
    public function forProductWithAttributes(Product $product, array $attributeValues): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'name' => $product->name . ' - ' . implode(', ', array_map(fn ($av) => $av->value, $attributeValues)),
            'sku' => $product->sku . '-' . implode('-', array_map(fn ($av) => $av->id, $attributeValues)),
        ])->hasAttached($attributeValues, [], 'attributeValues');
    }

    /**
     * Example: Zapatos Azules Talla 40
     */
    public function shoes(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Zapatos Formal Cuero',
            'sku' => 'ZAP-FORMAL-001',
            'weight' => 1.2,
        ]);
    }
}
