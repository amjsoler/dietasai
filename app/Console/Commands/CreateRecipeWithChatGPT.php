<?php

namespace App\Console\Commands;

use App\Http\Controllers\RecipeController;
use Illuminate\Console\Command;

class CreateRecipeWithChatGPT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-recipe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea una nueva receta haciendo uso de chatGPT y la almacena en la base de datos.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        return (new RecipeController())->createNewRecipeFromChatGPT();
    }
}
