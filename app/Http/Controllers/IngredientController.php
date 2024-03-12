<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function getAllIngredients()
    {
        return response()->json(Ingredient::all());
    }
}
