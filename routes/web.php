<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::get("/procesar-receta",
    function(Request $request) {
        if($request->has("pass") && $request->get("pass") === Config::get("telescope.telescope_pass")){

            if($request->has("recipe")) {
                $rawRecipe = $request->get("recipe");

                $recipe = json_decode($rawRecipe);

                return (new RecipeController())->storeRecipe($recipe);
            }
            else{
                abort(422, "No se ha enviado ninguna receta");
            }
        }
        else{
            abort(403);
        }
    });

Route::get("/prompt",
    function() {
        return view("prompt");
    })->middleware('telescopeauth')->name("getprompt");

Route::post("/prompt", [RecipeController::class, "prompt"])->middleware('telescopeauth')->name("postprompt");
