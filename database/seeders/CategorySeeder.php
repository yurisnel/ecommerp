<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories with placeholder images
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices and accessories',
            'image' => 'https://via.placeholder.com/400x400/1a73e8/FFFFFF?text=ELECTRONICS',
            'status' => 'active',
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'Apparel and fashion items',
            'image' => 'https://via.placeholder.com/400x400/ea4335/FFFFFF?text=CLOTHING',
            'status' => 'active',
        ]);

        $home = Category::create([
            'name' => 'Home & Garden',
            'slug' => 'home-garden',
            'description' => 'Home and garden products',
            'image' => 'https://via.placeholder.com/400x400/34a853/FFFFFF?text=HOME',
            'status' => 'active',
        ]);

        $sports = Category::create([
            'name' => 'Sports & Outdoors',
            'slug' => 'sports-outdoors',
            'description' => 'Sports equipment and outdoor gear',
            'image' => 'https://via.placeholder.com/400x400/fbbc04/FFFFFF?text=SPORTS',
            'status' => 'active',
        ]);

        // Create subcategories for Electronics
        Category::create([
            'name' => 'Computing',
            'slug' => 'computing',
            'description' => 'Computers and computing devices',
            'parent_id' => $electronics->id,
            'image' => 'https://via.placeholder.com/400x400/4285f4/FFFFFF?text=COMPUTING',
            'status' => 'active',
        ]);

        Category::create([
            'name' => 'Mobile Devices',
            'slug' => 'mobile-devices',
            'description' => 'Smartphones and tablets',
            'parent_id' => $electronics->id,
            'image' => 'https://via.placeholder.com/400x400/5b7ff5/FFFFFF?text=MOBILE',
            'status' => 'active',
        ]);

        Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'description' => 'Tech accessories',
            'parent_id' => $electronics->id,
            'image' => 'https://via.placeholder.com/400x400/6d7fff/FFFFFF?text=ACCESSORIES',
            'status' => 'active',
        ]);

        // Create subcategories for Clothing
        Category::create([
            'name' => "Men's Clothing",
            'slug' => 'mens-clothing',
            'description' => "Men's apparel",
            'parent_id' => $clothing->id,
            'image' => 'https://via.placeholder.com/400x400/f08080/FFFFFF?text=MENS',
            'status' => 'active',
        ]);

        Category::create([
            'name' => "Women's Clothing",
            'slug' => 'womens-clothing',
            'description' => "Women's apparel",
            'parent_id' => $clothing->id,
            'image' => 'https://via.placeholder.com/400x400/ff69b4/FFFFFF?text=WOMENS',
            'status' => 'active',
        ]);

        Category::create([
            'name' => 'Footwear',
            'slug' => 'footwear',
            'description' => 'Shoes and footwear',
            'parent_id' => $clothing->id,
            'image' => 'https://via.placeholder.com/400x400/ff8080/FFFFFF?text=FOOTWEAR',
            'status' => 'active',
        ]);

        // Create subcategories for Home & Garden
        Category::create([
            'name' => 'Furniture',
            'slug' => 'furniture',
            'description' => 'Home furniture',
            'parent_id' => $home->id,
            'image' => 'https://via.placeholder.com/400x400/80d080/FFFFFF?text=FURNITURE',
            'status' => 'active',
        ]);

        Category::create([
            'name' => 'Kitchen & Dining',
            'slug' => 'kitchen-dining',
            'description' => 'Kitchen equipment and dining items',
            'parent_id' => $home->id,
            'image' => 'https://via.placeholder.com/400x400/90ee90/FFFFFF?text=KITCHEN',
            'status' => 'active',
        ]);
    }
}
