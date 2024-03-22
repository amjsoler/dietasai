<?php

namespace App\Jobs;

use App\Http\Controllers\RecipeController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        Log::debug("CrearListadoRecetasJob::handle" . json_encode($this->context) . json_encode($this->prompt) . json_encode($this->numRecipes));

        (new RecipeController())->createRecipeList($this->context, $this->prompt, $this->numRecipes);
    }
}
