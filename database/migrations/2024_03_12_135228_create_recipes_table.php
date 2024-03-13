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
            $table->text('ingredients');
            $table->text('steps');
            $table->integer("kcal")->index();
            $table->integer("protein");
            $table->integer("carbs");
            $table->integer("fat");
            $table->enum("healthyness", [0, 1, 2])->index();
            $table->integer("preparation_time")->index();
            $table->integer("difficulty")->index();
            $table->string("allergens")->index();
            $table->string("food_restrictions")->index();
            $table->string("day_moment")->index();
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
