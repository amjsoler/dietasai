<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Route;


Route::get('/crear-receta', [RecipeController::class, 'createNewRecipeFromChatGPT']);
