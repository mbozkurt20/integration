<?php


use Illuminate\Support\Facades\Route;

Route::middleware('check.url')->group(function () {
// Webhook adresleri, sipariş ekleme ve iptal edilen siparişler
    Route::post('order', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::post('order/cancel', [\App\Http\Controllers\Api\OrderController::class, 'cancel']);

//------------------BUSINESS----------------||
    Route::get('business', [\App\Http\Controllers\Api\BusinessController::class, 'index']);
    Route::post('business', [\App\Http\Controllers\Api\BusinessController::class, 'store']);
//------------------BUSINESS----------------||

//------------------RESTAURANT----------------||
    Route::get('restaurants', [\App\Http\Controllers\Api\RestaurantController::class, 'index']);
    Route::post('restaurant', [\App\Http\Controllers\Api\RestaurantController::class, 'store']);
    Route::post('restaurant/{restaurantId}/provider/{providerId}', [\App\Http\Controllers\Api\RestaurantProviderController::class, 'store']);
//------------------RESTAURANT----------------||

//payment methods update endpoint
    Route::get('payment-methods', [\App\Http\Controllers\Api\IntegrationController::class, 'paymentMethods']);
});
