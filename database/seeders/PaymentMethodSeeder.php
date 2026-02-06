<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Cash',
                'code' => 'CASH',
                'type' => 'cash',
                'description' => 'Cash payment',
                'requires_config' => false,
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Credit Card',
                'code' => 'CREDIT_CARD',
                'type' => 'card',
                'description' => 'Credit card payment',
                'requires_config' => true,
                'config_fields' => [
                    ['key' => 'gateway', 'label' => 'Payment Gateway', 'type' => 'select', 'options' => ['stripe', 'paypal']],
                    ['key' => 'api_key', 'label' => 'API Key', 'type' => 'text', 'encrypted' => true],
                    ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'encrypted' => true],
                ],
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'name' => 'Debit Card',
                'code' => 'DEBIT_CARD',
                'type' => 'card',
                'description' => 'Debit card payment',
                'requires_config' => true,
                'sort_order' => 3,
                'status' => 'active',
            ],
            [
                'name' => 'Bank Transfer',
                'code' => 'BANK_TRANSFER',
                'type' => 'bank_transfer',
                'description' => 'Direct bank transfer',
                'requires_config' => false,
                'sort_order' => 4,
                'status' => 'active',
            ],
            [
                'name' => 'PayPal',
                'code' => 'PAYPAL',
                'type' => 'digital_wallet',
                'description' => 'PayPal payment',
                'requires_config' => true,
                'config_fields' => [
                    ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text'],
                    ['key' => 'secret', 'label' => 'Secret', 'type' => 'text', 'encrypted' => true],
                    ['key' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => ['sandbox', 'live']],
                ],
                'sort_order' => 5,
                'status' => 'inactive',
            ],
            [
                'name' => 'Stripe',
                'code' => 'STRIPE',
                'type' => 'digital_wallet',
                'description' => 'Stripe payment',
                'requires_config' => true,
                'config_fields' => [
                    ['key' => 'publishable_key', 'label' => 'Publishable Key', 'type' => 'text'],
                    ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'text', 'encrypted' => true],
                ],
                'sort_order' => 6,
                'status' => 'inactive',
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}
