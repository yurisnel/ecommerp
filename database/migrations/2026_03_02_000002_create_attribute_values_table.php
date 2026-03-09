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
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->string('value'); // e.g., Azul, 40, Rojo
            $table->string('value_es')->nullable(); // Spanish translation
            $table->string('color_code')->nullable(); // Hex color for visual representation
            $table->string('image')->nullable(); // Image for the value (e.g., color swatch)
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add unique index for attribute_id + value combination
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->unique(['attribute_id', 'value'], 'attr_val_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};
