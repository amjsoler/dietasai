<?php

use App\Jobs\CrearListadoRecetasJob;
use App\Jobs\CrearReceta;
use Illuminate\Support\Facades\Artisan;

Artisan::command('crear-receta {numRecetas}', function (int $numRecetas) {
    for($i = 0; $i < $numRecetas; $i++) {
        CrearReceta::dispatch(null, null)->delay(now()->addMinutes(5*$i));
    }
})->purpose('Genera una nueva receta mediante ChatGPT y la almacena en BD.');



Artisan::command('crear-listado-recetas {numRecetas}', function (int $numRecetas) {
    CrearListadoRecetasJob::dispatch(null, null, $numRecetas);
})->purpose('Genera un listado de recetas mediante ChatGPT y las almacena en BD excluyendo las repetidas.');
