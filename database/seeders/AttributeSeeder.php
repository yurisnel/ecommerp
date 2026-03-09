<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Color attribute with values
        $color = Attribute::create([
            'name' => 'Color',
            'code' => 'color',
            'description' => 'Color del producto',
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true,
            'sort_order' => 1,
        ]);

        $colorValues = [
            ['value' => 'Azul', 'value_es' => 'Azul', 'color_code' => '#0000FF', 'sort_order' => 1],
            ['value' => 'Rojo', 'value_es' => 'Rojo', 'color_code' => '#FF0000', 'sort_order' => 2],
            ['value' => 'Negro', 'value_es' => 'Negro', 'color_code' => '#000000', 'sort_order' => 3],
            ['value' => 'Blanco', 'value_es' => 'Blanco', 'color_code' => '#FFFFFF', 'sort_order' => 4],
            ['value' => 'Verde', 'value_es' => 'Verde', 'color_code' => '#008000', 'sort_order' => 5],
            ['value' => 'Amarillo', 'value_es' => 'Amarillo', 'color_code' => '#FFFF00', 'sort_order' => 6],
        ];

        foreach ($colorValues as $val) {
            $color->values()->create($val);
        }

        // Create Talla attribute with values
        $talla = Attribute::create([
            'name' => 'Talla',
            'code' => 'talla',
            'description' => 'Talla del producto',
            'type' => 'select',
            'is_required' => true,
            'is_filterable' => true,
            'sort_order' => 2,
        ]);

        $tallaValues = [
            ['value' => '38', 'value_es' => '38', 'sort_order' => 1],
            ['value' => '39', 'value_es' => '39', 'sort_order' => 2],
            ['value' => '40', 'value_es' => '40', 'sort_order' => 3],
            ['value' => '41', 'value_es' => '41', 'sort_order' => 4],
            ['value' => '42', 'value_es' => '42', 'sort_order' => 5],
            ['value' => '43', 'value_es' => '43', 'sort_order' => 6],
            ['value' => '44', 'value_es' => '44', 'sort_order' => 7],
        ];

        foreach ($tallaValues as $val) {
            $talla->values()->create($val);
        }

        // Create Material attribute with values
        $material = Attribute::create([
            'name' => 'Material',
            'code' => 'material',
            'description' => 'Material del producto',
            'type' => 'select',
            'is_required' => false,
            'is_filterable' => true,
            'sort_order' => 3,
        ]);

        $materialValues = [
            ['value' => 'Cuero', 'value_es' => 'Cuero', 'sort_order' => 1],
            ['value' => 'Tela', 'value_es' => 'Tela', 'sort_order' => 2],
            ['value' => 'Sintético', 'value_es' => 'Sintético', 'sort_order' => 3],
            ['value' => 'Lona', 'value_es' => 'Lona', 'sort_order' => 4],
        ];

        foreach ($materialValues as $val) {
            $material->values()->create($val);
        }

        $this->command->info('Attributes and values seeded successfully!');
    }
}
