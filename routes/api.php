<?php


use Illuminate\Support\Facades\Route;

// Webhook adresleri, sipariş ekleme ve iptal edilen siparişler
Route::post('order',[\App\Http\Controllers\OrderController::class,'store']);
Route::post('order/cancel',[\App\Http\Controllers\OrderController::class,'cancel']);


//payment methods update endpoint
Route::get('payment-methods',[\App\Http\Controllers\IntegrationController::class,'paymentMethods']);
