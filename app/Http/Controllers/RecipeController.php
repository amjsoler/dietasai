<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetDietFormRequest;
use App\Models\Recipe;

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
}
