<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->city . ' Warehouse',
            'code' => 'WH-' . strtoupper($this->faker->unique()->lexify('???')),
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'manager_id' => null, // Can assign manually
            'status' => 'active',
        ];
    }
}
