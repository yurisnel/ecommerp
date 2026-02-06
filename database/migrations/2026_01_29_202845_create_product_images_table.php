<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing data from products table to product_images table
        $products = \Illuminate\Support\Facades\DB::table('products')->get();
        foreach ($products as $product) {
            if ($product->images) {
                $images = json_decode($product->images, true);
                if (is_array($images)) {
                    foreach ($images as $index => $imageUrl) {
                        \Illuminate\Support\Facades\DB::table('product_images')->insert([
                            'product_id' => $product->id,
                            'url' => $imageUrl,
                            'is_default' => ($imageUrl === $product->image),
                            'sort_order' => $index,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Optional: Remove columns from products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image', 'images']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
