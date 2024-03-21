<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetDietFormRequest;
use App\Http\Requests\PromptRequest;
use App\Jobs\CrearReceta;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Support\Facades\Http;

class RecipeController extends Controller
{
    public function prompt(PromptRequest $request) {
        for($i=0;$i<$request->num_recipes;$i++) {
            CrearReceta::dispatch($request->context, $request->prompt)->delay(now()->addSeconds(5*$i));
        }

        return view("prompt", ["result" => "Receta generada correctamente"]);
    }

    public function getDiet(GetDietFormRequest $request) {
        $cantGenerateDiet = false;

        $dietaGenerada = [];

        //Defino la cantidad máxima de calorías por día
        $dailyKcal = $request->kcal;

        //Defino las comidas de cada día
        $meals = $request->meals_included;

        $daysOfTheWeek = ["lunes", "martes", "miércoles", "jueves", "viernes", "sábado", "domingo"];

        $importanceMeals = ["comida", "cena", "desayuno", "almuerzo", "merienda"];

        $orderedMeals = ["desayuno", "almuerzo", "comida", "merienda", "cena"];

        //Genero la dieta de cada día de la semana
        foreach($daysOfTheWeek as $day) {
            $dietaGenerada[$day] = [];
            $kcalAccumulatedDay = 0;

            while($kcalAccumulatedDay < $dailyKcal) {
                foreach($importanceMeals as $actualMeal) {
                    if(in_array($actualMeal, $meals)){
                        $recipe = $this->getRandomRecipe($request, $actualMeal, $dailyKcal - $kcalAccumulatedDay);

                        if($recipe == null) {
                            $cantGenerateDiet = true;
                            break;
                        }else{
                            $dietaGenerada[$day][$actualMeal][] = $recipe;
                            $kcalAccumulatedDay += $recipe->kcal;
                        }
                    }
                }
            }

            //Ordenamos el array resultante en función del momento del día
            $dietaGenerada[$day] = array_merge(array_flip($orderedMeals), $dietaGenerada[$day]);

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

        $recipe->where("kcal", "<=", $maxKcal+150)->orWhere("kcal", ">=", $maxKcal-150);

        return $recipe->first();
    }

    public function createNewRecipeFromChatGPT($context = null, $prompt = null)
    {
        $receta = $this->getRecipeFromGPT($context, $prompt);

        $receta = $this->storeRecipe($receta);

        return $receta;
    }

    public function storeRecipe($receta)
    {
        $recetaModel = new Recipe();
        $recetaModel->name = $receta->nombre;
        $recetaModel->ingredients = json_encode($receta->ingredientes);
        $recetaModel->steps = json_encode($receta->pasos_elaboracion);
        $recetaModel->kcal = $receta->kcal;
        $recetaModel->protein = $receta->proteinas;
        $recetaModel->carbs = $receta->hidratos;
        $recetaModel->fat = $receta->grasas;
        $recetaModel->healthyness = "".$receta->saludable;
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

        return $recetaModel;
    }

    private function getRecipeFromGPT($customContext = null, $customPrompt = null){
        $response = Http::withHeaders([
            "Authorization" => "Bearer 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",
        ])
            ->contentType("application/json")
            ->withBody('{
    "model": "gpt-3.5-turbo",
    "messages": [
      {
        "role": "user",
        "content": "' . ($customContext ?? "Eres un chef experimentado y sabes miles de recetas que has aprendido durante décadas de trabajo") . '. ' . ($customPrompt ?? "Dame una receta") . '. Utiliza el siguiente número ('.now()->getTimestamp().') a modo de semilla para intentar no repetir la receta. La respuesta deberá ser únicamente un JSON válido como el siguiente (sin comentarios): {nombre:\"\",ingredientes:[{nombre:\"\",cantidad:\"\",unidades:\"\"}],pasos_elaboracion:[\"\"/*Sin coma al final del último paso*/],kcal:X,proteinas:X,hidratos:X,grasas:X,saludable:enum(0,1,2),/*0:poco saludable,1:equilibrada,2:saludable*/tiempo_preparacion:X,/*Minutos*/dificultad:enum(0,1,2),/*0:Fácil,1:dificultad media,2:Difícil*/alergenos:[\"\"],/*Posibles alergenos:cacahuete,frutossecos,mariscos,pescados,leche,huevos,trigo,soja (Dejar el array vacío si no contiene ninguno de los alergenos anteriores)*/restricciones_alimentarias:[\"\"],/*Posibles restricciones:vegetariana,vegana,glutenfree,lacteosfree (Dejar el array vacío si no cumple ninguna de las restricciones anteriores)*/momento_dia:[\"\"]/*Posibles valores:desayuno,almuerzo,comida,merienda,cena*/} Recuerda validar el JSON para que sea correcto y se pueda hacer un json_decode con él en PHP sin problemas."
      }
    ]
  }')
            ->post('https://api.openai.com/v1/chat/completions');

        $receta = json_decode($response);
        $receta = $receta->choices[0]->message->content;

        return json_decode($receta);
    }
}
