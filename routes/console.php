<?php

use App\Jobs\CrearReceta;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('crear-receta {numRecetas}', function (int $numRecetas) {
    for($i = 0; $i < $numRecetas; $i++) {
        CrearReceta::dispatch()->delay(now()->addSeconds(5*$i));
    }

})->purpose('Genera una nueva receta mediante ChatGPT y la almacena en BD.');
