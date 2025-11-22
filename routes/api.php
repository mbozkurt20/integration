<?php


use Illuminate\Support\Facades\Route;

Route::post('order',[\App\Http\Controllers\EntegraController::class,'store']);
