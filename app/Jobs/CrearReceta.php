<?php

namespace App\Jobs;

use App\Http\Controllers\RecipeController;
use App\Models\Ingredient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrearReceta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $context;

    protected $prompt;

    /**
     * Create a new job instance.
     */
    public function __construct($context, $prompt)
    {
        $this->context = $context;
        $this->prompt = $prompt;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new RecipeController())->createNewRecipeFromChatGPT($this->context, $this->prompt);
    }
}
