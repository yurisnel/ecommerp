<?php

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_number' => 'CUST-' . $this->faker->unique()->numberBetween(10000, 99999),
            'customer_group_id' => CustomerGroup::inRandomOrder()->first()?->id ?? null,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'type' => $this->faker->randomElement(['individual', 'business']),
            'status' => 'active'
        ];
    }
}
