<?php

namespace App\Jobs;

use App\Http\Controllers\RecipeController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrearListadoRecetasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $context;
    protected $prompt;
    protected $numRecipes;

    public function __construct($context, $prompt, $numRecipes)
    {
        $this->context = $context;
        $this->prompt = $prompt;
        $this->numRecipes = $numRecipes;
    }

    public function handle(): void
    {
        (new RecipeController())->createRecipeList($this->context, $this->prompt, $this->numRecipes);
    }
}
