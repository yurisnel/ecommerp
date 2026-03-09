<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attribute>
 */
class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Create attribute with values
     */
    public function withValues(array $values = []): static
    {
        return $this->has(
            AttributeValue::factory()->count(count($values))->sequence(...array_map(
                fn ($value) => ['value' => $value],
                $values
            ))
        );
    }

    /**
     * Color attribute
     */
    public function color(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Color',
            'code' => 'color',
        ])->withValues(['Azul', 'Rojo', 'Negro', 'Blanco', 'Verde', 'Amarillo']);
    }

    /**
     * Size attribute
     */
    public function size(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Talla',
            'code' => 'talla',
        ])->withValues(['38', '39', '40', '41', '42', '43', '44', '45']);
    }

    /**
     * Material attribute
     */
    public function material(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Material',
            'code' => 'material',
        ])->withValues(['Cuero', 'Tela', 'Sintético', 'Lona']);
    }
}
