<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvisionalRecipe extends Model
{
    use HasFactory;

    protected $table = "provisional_recipes";

    protected $fillable = ["name"];

    protected $hidden = ["id", "created_at", "updated_at"];
}
