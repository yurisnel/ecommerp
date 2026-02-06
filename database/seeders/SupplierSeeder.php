<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Supplier::create([
            'name' => 'Supplier Global Corp',
            'company_name' => 'Global Corp S.A.',
            'tax_id' => '123456789',
            'email' => 'contact@globalcorp.com',
            'status' => 'active'
        ]);

        \App\Models\Supplier::create([
            'name' => 'Tech Solutions Ltd',
            'company_name' => 'Tech Solutions Limited',
            'tax_id' => '987654321',
            'email' => 'info@techsolutions.com',
            'status' => 'active'
        ]);

        \App\Models\Supplier::create([
            'name' => 'General Trading Co',
            'company_name' => 'General Trading Company',
            'tax_id' => '456789123',
            'email' => 'sales@gentrading.com',
            'status' => 'active'
        ]);
    }
}
