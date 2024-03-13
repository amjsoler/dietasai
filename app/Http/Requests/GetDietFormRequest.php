<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDietFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "meals_included" => "required|array",
            "meals_included.*" => "string|in:desayuno,almuerzo,comida,merienda,cena",
            "healthyness" => "required|integer|between:0,2",
            "max_time" => "required|integer|between:5,120",
            "difficulty" => "required|integer|between:0,2",
            "allergies" => "array",
            "allergies.*" => "string|in:cacahuete,frutossecos,mariscos,pescados,leche,huevos,trigo,soja",
            "food_restrictions" => "array",
            "food_restrictions.*" => "string|in:vegetariana,vegana,gluten,lacteos",
            "ingredients_excluded" => "array",
            "ingredients_excluded.*" => "string",
            "kcal" => "required|integer|between:0,5000",
        ];
    }
}
