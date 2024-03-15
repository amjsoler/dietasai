<?php

namespace App\Jobs;

use App\Console\Commands\CreateRecipeWithChatGPT;
use App\Http\Controllers\RecipeController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrearReceta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recetaCreada = (new RecipeController())->createNewRecipeFromChatGPT();

        //TODO: Regenerar ingredientes
    }
}
