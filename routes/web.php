<?php

use Illuminate\Support\Facades\Route;

// Route::get('/{any}', function () {
//     return view('welcome');
// })->where('any', '.*');

Route::get('/{any}', function () {
    return view('app'); // ← ИЗМЕНИТЕ 'welcome' на 'app'
})->where('any', '.*');
