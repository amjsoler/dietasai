<?php

use App\Jobs\CrearReceta;
use Illuminate\Support\Facades\Artisan;

Artisan::command('crear-receta {numRecetas}', function (int $numRecetas) {
    for($i = 0; $i < $numRecetas; $i++) {
        CrearReceta::dispatch(null, null)->delay(now()->addMinutes(10));
    }

})->purpose('Genera una nueva receta mediante ChatGPT y la almacena en BD.');
