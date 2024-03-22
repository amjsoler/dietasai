<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

//UI To generate a provisional recipe list
Route::get("/prompt-recipe-list",
    function() {
        Log::debug("GET /prompt-recipe-list");

        return view("prompt-recipe-list");
    }
)->name("getpromptrecipelist");

//Post to generate a provisional recipe list
Route::post("/prompt-recipe-list",
    [RecipeController::class, "promptRecipeList"]
)->name("postpromptrecipelist");
