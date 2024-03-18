<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetDietFormRequest;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    public function getDiet(GetDietFormRequest $request) {
        $cantGenerateDiet = false;

        $dietaGenerada = [];

        //Defino la cantidad máxima de calorías por día
        $dailyKcal = $request->kcal;

        //Defino las comidas de cada día
        $meals = $request->meals_included;

        $daysOfTheWeek = ["lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo"];

        $orderedMeals = ["desayuno", "almuerzo", "comida","merienda", "cena"];

        $importanceMeals = ["desayuno", "comida", "cena", "almuerzo", "merienda"];

        foreach($daysOfTheWeek as $day) {
            $kcalAccumulatedDay = 0;

            $dietaGenerada[$day] = [];

            foreach($meals as $meal) {
                $recipe = $this->getRandomRecipe($request, $meal, $dailyKcal - $kcalAccumulatedDay);

                if(!$recipe) {
                    $cantGenerateDiet = true;
                    break;
                }

                $kcalAccumulatedDay += $recipe->kcal;
                $dietaGenerada[$day][$meal][] = $recipe;
            }
/*
            while($kcalAccumulatedDay < $dailyKcal) {
                $meal = $orderedMeals[rand(0, 4)];

                $recipe = $this->getRandomRecipe($request, $meal, $dailyKcal - $kcalAccumulatedDay);

                if(!$recipe) {
                    $cantGenerateDiet = true;
                    break;
                }

                $kcalAccumulatedDay += $recipe->kcal;
                $dietaGenerada[$day][$meal][] = $recipe;
            }*/
        }


        if($cantGenerateDiet) {
            return response()->json(["message" => "No hemos podido generar la dieta con las restricciones proporcionadas. Intenta ser un poco más laxo"], 400);
        }
        else{
            return response()->json($dietaGenerada, 200);
        }

    }

    private function getRandomRecipe($request, $meal, $maxKcal) {
        $recipe = Recipe::inRandomOrder();

        $recipe->where("day_moment", "like", "%$meal%");
        $recipe->where("healthyness", ">=", $request->healthyness);
        $recipe->where("preparation_time", "<=", $request->max_time);
        $recipe->where("difficulty", "<=", $request->difficulty);

        if($request->allergies) {
            $recipe->where(function($query) use ($request) {
                foreach($request->allergies as $allergy) {
                    $query->where("allergens", "not like", "%$allergy%");
                }
            });
        }

        if($request->food_restrictions) {
            $recipe->where(function($query) use ($request) {
                foreach($request->food_restrictions as $restriction) {
                    $query->where("food_restrictions", "like", "%$restriction%");
                }
            });
        }

        if($request->ingredients_excluded) {
            $recipe->where(function($query) use ($request) {
                foreach($request->ingredients_excluded as $ingredient) {
                    $query->where("ingredients", "not like", "%$ingredient%");
                }
            });

        }

        $recipe->where("kcal", "<=", $maxKcal);

        return $recipe->first();
    }

    public function createNewRecipeFromChatGPT()
    {
        //TODO: Sustituir este JSON por el que se obtenga de la API de OpenAI
        $receta = '{
  "nombre": "Pollo al horno con verduras",
  "ingredientes": [
    {"nombre": "Pechuga de pollo", "cantidad": 2, "unidades": "piezas"},
    {"nombre": "Pimiento rojo", "cantidad": 1, "unidades": "unidad"},
    {"nombre": "Pimiento verde", "cantidad": 1, "unidades": "unidad"},
    {"nombre": "Cebolla", "cantidad": 1, "unidades": "unidad"},
    {"nombre": "Calabacín", "cantidad": 1, "unidades": "unidad"},
    {"nombre": "Aceite de oliva", "cantidad": 2, "unidades": "cucharadas"},
    {"nombre": "Sal", "cantidad": 1, "unidades": "pizca"},
    {"nombre": "Pimienta", "cantidad": 1, "unidades": "pizca"},
    {"nombre": "Tomillo", "cantidad": 1, "unidades": "ramita"}
  ],
  "pasos_elaboracion": [
    "Precalienta el horno a 200°C.",
    "Corta las pechugas de pollo en trozos medianos y colócalas en una bandeja para hornear.",
    "Corta los pimientos, la cebolla y el calabacín en tiras y agrégalos a la bandeja junto con el pollo.",
    "Rocía todo con aceite de oliva y sazona con sal, pimienta y tomillo al gusto.",
    "Hornea durante 25-30 minutos o hasta que el pollo esté bien cocido y las verduras estén tiernas.",
    "Sirve caliente y disfruta."
  ],
  "kcal": 350,
  "proteinas": 30,
  "hidratos": 15,
  "grasas": 18,
  "saludable": 2,
  "tiempo_preparacion": 40,
  "dificultad": 1,
  "alergenos": [],
  "restricciones_alimentarias": ["glutenfree"],
  "momento_dia": ["cena"]
}
';

        $receta = json_decode($receta);

        $recetaModel = new Recipe();
        $recetaModel->name = $receta->nombre;
        $recetaModel->ingredients = json_encode($receta->ingredientes);
        $recetaModel->steps = json_encode($receta->pasos_elaboracion);
        $recetaModel->kcal = $receta->kcal;
        $recetaModel->protein = $receta->proteinas;
        $recetaModel->carbs = $receta->hidratos;
        $recetaModel->fat = $receta->grasas;
        $recetaModel->healthyness = $receta->saludable;
        $recetaModel->preparation_time = $receta->tiempo_preparacion;
        $recetaModel->difficulty = $receta->dificultad;
        $recetaModel->allergens = json_encode($receta->alergenos);
        $recetaModel->food_restrictions = json_encode($receta->restricciones_alimentarias);
        $recetaModel->day_moment = json_encode($receta->momento_dia);

        $recetaModel->save();

        foreach($receta->ingredientes as $ingredient) {
            //Comprobamos si el ingrediente acual se encuentra en la base de datos
            $ingredientSelected = Ingredient::where("name", $ingredient->nombre);

            //Si no está lo insertamos
            if($ingredientSelected->first() == null) {
                $ingredientSelected = new Ingredient();
                $ingredientSelected->name = $ingredient->nombre;
                $ingredientSelected->unit = $ingredient->unidades;

                $ingredientSelected->save();
            }
        }

        return $receta;
    }
}
