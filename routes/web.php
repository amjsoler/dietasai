<?php

use Illuminate\Support\Facades\Route;

Route::get('/forbidden', function () {
    return "You are not allowed to access this page!";
})->name('forbidden');
