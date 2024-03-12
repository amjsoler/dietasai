<?php

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get("/ingredients",
[IngredientController::class, "getAllIngredients"]);

Route::get("/get-diet",
[RecipeController::class, "getDiet"]);
