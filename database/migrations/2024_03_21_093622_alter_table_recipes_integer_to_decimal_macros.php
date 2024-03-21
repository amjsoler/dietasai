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
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal("protein", 8, 2)->change();
            $table->decimal("carbs", 8, 2)->change();
            $table->decimal("fat", 8, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->integer("protein")->change();
            $table->integer("carbs")->change();
            $table->integer("fat")->change();
        });
    }
};
