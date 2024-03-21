<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetDietFormRequest;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Http;
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
        $receta = $this->getRecipeFromGPT();

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

    private function getRecipeFromGPT(){
        $response = Http::withHeaders([
            "Authorization" => "Bearer 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",
        ])
            ->contentType("application/json")
            ->withBody('{
    "model": "gpt-3.5-turbo",
    "messages": [
      {
        "role": "system",
        "content": "Eres un chef experimentado y sabes miles de recetas que has aprendido durante décadas de trabajo"
      },
      {
        "role": "user",
        "content": "Dame una receta utilizando el timestamp actual a modo de semilla para no repetir. La respuesta deberá ser únicamente un JSON como el siguiente: {nombre:\"\",ingredientes:[{nombre:\"\",cantidad:\"\",unidades:\"\"}],pasos_elaboracion:[\"\"],kcal:X,proteinas:X,hidratos:X,grasas:X,saludable:enum(0,1,2),/*0:poco saludable,1:equilibrada,2:saludable*/tiempo_preparacion:X,/*Minutos*/dificultad:enum(0,1,2),/*0:Facil,1:dificultad media,2:Dificil*/alergenos:[\"\"],/*Posibles valores:cacahuete,frutossecos,mariscos,pescados,leche,huevos,trigo,soja*/restricciones_alimentarias:[\"\"],/*Posibles valores:vegetariana,vegana,glutenfree,lacteosfree*/momento_dia:[\"\"]/*Posibles valores:desayuno,almuerzo,comida,merienda,cena*/}"
      }
    ]
  }')
            ->post('https://api.openai.com/v1/chat/completions');

        $receta = json_decode($response);
        $receta = $receta->choices[0]->message->content;

        return json_decode($receta);
    }
}
