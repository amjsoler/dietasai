<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIHelper
{
    private static function openAIRequest($content) {
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . Config::get("app.open_ai_token"),
        ])
            ->contentType("application/json")
            ->withBody('{
    "model": "gpt-3.5-turbo",
    "messages": [
      {
        "role": "user",
        "content": "' . $content . '"
      }
    ]
  }')
            ->post('https://api.openai.com/v1/chat/completions');

        $response = json_decode($response);
        $response = $response->choices[0]->message->content;

        return json_decode($response);
    }

    /**
     * Crea un listado de recetas a partir de un contexto y una petición personalizada
     *
     * @param $customContext
     * @param $customPrompt
     * @param $numRecipes
     *
     * @return mixed
     */
    public static function getRecipeListFromGPT($customContext, $customPrompt, $numRecipes) {
        Log::debug("getRecipeListFromGPT" . json_encode($customContext) . json_encode($customPrompt) . json_encode($numRecipes));

        $content = ($customContext ?? "Eres un chef veterano con décadas de experiencia a tus espaldas y ahora estás aquí para compartir tu conocimiento de más de un millón de recetas") . '. ';
        $content .= ($customPrompt ?? "Dame un listado de $numRecipes recetas") . '. ';
        $content .= 'Usa el número '.(now()->getTimestamp()%10000).' a modo de semilla para intentar no repetir ninguna receta facilitada en peticiones anteriores. La respuesta deberá ser un Array de cadenas de texto con los nombres de las recetas. Recuerda validar el Array para que sea correcto y tenga un formato tipo [\"Sopa de champiñones\", \"Tarta de queso\", \"Ensalada de pasta\", \"Pollo al curry\", ...]';

        return self::openAIRequest($content);
    }

    /**
     * Crea una receta con el formato especificado a partir de un nombre de receta
     *
     * @param $nombre
     *
     * @return mixed
     */
    public static function getRecipeFromGPTGivenName($nombre)
    {
        $content = ($customContext ?? "Eres un chef veterano con décadas de experiencia a tus espaldas") . '. ';
        $content .= 'Dame la receta completa de: ' . $nombre . '. La respuesta deberá ser únicamente un JSON válido como el siguiente (sin comentarios): {nombre:\"\",ingredientes:[{nombre:\"\",cantidad:\"\",unidades:\"\"}],pasos_elaboracion:[\"\"/*Sin coma al final del último paso*/],kcal:X,proteinas:X,hidratos:X,grasas:X,saludable:enum(0,1,2),/*0:poco saludable,1:equilibrada,2:saludable*/tiempo_preparacion:X,/*Minutos*/dificultad:enum(0,1,2),/*0:Fácil,1:dificultad media,2:Difícil*/alergenos:[\"\"],/*Posibles alergenos:cacahuete,frutossecos,mariscos,pescados,leche,huevos,trigo,soja (Dejar el array vacío si no contiene ninguno de los alergenos anteriores)*/restricciones_alimentarias:[\"\"],/*Posibles restricciones:vegetariana,vegana,glutenfree,lacteosfree (Dejar el array vacío si no cumple ninguna de las restricciones anteriores)*/momento_dia:[\"\"]/*Posibles valores:desayuno,almuerzo,comida,merienda,cena*/} Recuerda validar el JSON para que sea correcto y se pueda hacer un json_decode con él en PHP sin problemas.';

        return self::openAIRequest($content);
    }
}
