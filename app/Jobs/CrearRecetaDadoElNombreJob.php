<?php

namespace App\Jobs;

use App\Http\Controllers\RecipeController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CrearRecetaDadoElNombreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $nombre;

    /**
     * Create a new job instance.
     */
    public function __construct($nombre)
    {
        $this->nombre = $nombre;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new RecipeController())->crearRecetaDadoNombre($this->nombre);
    }
}
