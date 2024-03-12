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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ingredients');
            $table->string('steps');
            $table->integer("kcla");
            $table->integer("protein");
            $table->integer("carbs");
            $table->integer("fat");
            $table->enum("healthyness", [0, 1, 2]);
            $table->integer("preparation_time");
            $table->integer("difficulty");
            $table->string("allergens");
            $table->string("food_restrictions");
            $table->string("day_moment");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
