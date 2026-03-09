<?php

namespace Database\Factories;

use App\Models\AttributeValue;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'value' => $this->faker->word(),
            'value_es' => null,
            'color_code' => $this->faker->hexColor(),
            'image' => null,
            'sort_order' => 0,
        ];
    }
}
