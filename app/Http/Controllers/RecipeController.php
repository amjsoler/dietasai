<?php

namespace App\Http\Controllers;

use App\Helpers\OpenAIHelper;
use App\Http\Requests\GetDietFormRequest;
use App\Http\Requests\PromptRequest;
use App\Jobs\CrearListadoRecetasJob;
use App\Jobs\CrearReceta;
use App\Jobs\CrearRecetaDadoElNombreJob;
use App\Models\Ingredient;
use App\Models\ProvisionalRecipe;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RecipeController extends Controller
{
    /**
     * Función que se encarga de recibir los parámetros del formulario y encolar la generación de recetas
     *
     * @param Request $request
     * @return View
     */
    public function promptRecipeList(Request $request)
    {
        Log::debug("POST /prompt-recipe-list: " . json_encode($request->all()));

        CrearListadoRecetasJob::dispatch($request->input("context"), $request->input("prompt"), $request->input("num_recipes") ?? 1);

        return view("prompt-recipe-list", ["result" => "Recetas encoladas para creación"]);
    }

    /**
     * A partir de la lista de recetas provisionales generadas por GPT, saca las recetas que no estén ya en la tabla de recipes y las inserta en la tabla de provisional-recipes
     *
     * @param $context String Contexto de la petición
     * @param $prompt String Prompt de la petición
     * @param $numRecetas int Número de recetas a generar
     *
     * @return array
     */
    public function createRecipeList($context, $prompt, $numRecetas)
    {
        Log::debug("RecipeController::createRecipeList" . json_encode($context) . json_encode($prompt) . json_encode($numRecetas));

        //Obtenemos el listado de recetas
        $recetas = OpenAIHelper::getRecipeListFromGPT($context, $prompt, $numRecetas);

        //Buscamos en la tabla de recipes si ya existe alguna de las recetas generadas
        $existingRecipes = Recipe::whereIn("name", $recetas)->get("name")->toArray();

        //También comprobamos que no se hayan insertado ya en la tabla de provisionales
        $existingProvisionalRecipes = ProvisionalRecipe::whereIn("name", $recetas)->get("name")->toArray();

        $recetasAInsertar =
            array_diff(
                $recetas,
                array_map(function($item){return $item["name"];}, $existingRecipes),
                array_map(function($item){return $item["name"];}, $existingProvisionalRecipes)
            );

        //Recorremos el array resultante y lo insertamos en la tabla de provisional-recipes
        $dataToInsert = [];
        foreach($recetasAInsertar as $receta) {
            $dataToInsert[] = ["name" => $receta];
        }

        ProvisionalRecipe::insert($dataToInsert);

        Log::debug("response: RecipeController::createRecipeList" . json_encode($dataToInsert));
        return $dataToInsert;
    }

    /**
     * Método que se encarga de procesar las recetas provisionales que se encuentran en la tabla de provisional-recipes
     *
     * @return void
     */
    public function procesarRecetasProvisionales() {
        //Leer las recetas provisionales disponibles
        $recetasProvisionales = ProvisionalRecipe::all();

        //Recorrer las recetas provisionales y encolar un job que se encargue de procesar cada una de ellas
        foreach($recetasProvisionales as $receta) {
            CrearRecetaDadoElNombreJob::dispatch($receta->name);
        }

        //Truncar la tabla de recetas provisionales
        ProvisionalRecipe::truncate();
    }

    /**
     * Método que se encarga de crear una receta a partir de un nombre dado
     *
     * @param $nombre String El nombre de la receta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function crearRecetaDadoNombre($nombre) {
        $response = OpenAIHelper::getRecipeFromGPTGivenName($nombre);

        $response = $this->storeRecipe($response);

        return response()->json($response, 200);
    }

    /**
     * Función encargada de almacenar una receta en base de datos y comprobar si los ingredientes de la misma ya se encuentran en la base de datos
     *
     * @param $receta
     *
     * @return Recipe
     */
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

    /**
     * Método que se encarga de recibir los parámetros del formulario y generar una dieta
     *
     * @param GetDietFormRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDiet(GetDietFormRequest $request) {
        //TODO: Falta comprobar esta función bien para asegurarnos de que genera las dietas correctamente
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

    /**
     * Devuelve una receta aleatoria que cumpla con los requisitos de la petición
     *
     * @param $request
     * @param $meal
     * @param $maxKcal
     *
     * @return mixed
     */
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
}
