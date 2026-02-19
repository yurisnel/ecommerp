<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); //'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#cccccc');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_statuses');
    }
};
