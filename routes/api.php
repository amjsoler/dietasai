<?php

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;

Route::get("/ingredients",
[IngredientController::class, "getAllIngredients"]);

Route::post("/get-diet",
[RecipeController::class, "getDiet"]);
