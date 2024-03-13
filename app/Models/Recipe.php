<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $hidden = ["id", "day_moment", 'created_at', 'updated_at'];

    protected $casts = [
        'ingredients' => 'json',
        'steps' => 'json',
    ];
}
