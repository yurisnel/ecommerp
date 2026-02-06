<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'label' => $this->faker->randomElement(['Home', 'Office', 'Warehouse', 'Store']),
            'contact_name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'address_line1' => $this->faker->streetAddress,
            'address_line2' => $this->faker->optional(0.3)->secondaryAddress,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'postal_code' => $this->faker->postcode,
            'country' => $this->faker->country,
            'type' => $this->faker->randomElement(['shipping', 'billing', 'both']),
            'is_default' => $this->faker->boolean(50),
            'notes' => $this->faker->optional()->sentence,
        ];
    }
}
