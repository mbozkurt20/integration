<?php

use App\Models\Restaurant;
use App\Services\RestaurantService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('restaurants.index');
});

Route::get('/restaurants', function () {
    return Restaurant::all();
});

Route::get('/restaurants/{id}', function ($id) {
    $restaurant = Restaurant::find($id);
    return view('restaurants.edit',compact('restaurant'));
});

Route::post('/restaurants', function (Request $request) {
    $restaurant = Restaurant::create($request->only(['name', 'website']));

    if ($restaurant){
        RestaurantService::setToken(config('integration.company_token'));
        RestaurantService::newRestaurant($request->only(['name']));
    }

    return $restaurant;
});

Route::put('/restaurants/{id}', function (Request $request, $id) {
    $restaurant = Restaurant::findOrFail($id);

    $data = $request->only([
        'name',
        'website',
        'getir',
        'yemeksepeti',
        'migros',
        'trendyol'
    ]);

    foreach ($data as $key => $value) {
        if (in_array($key, ['getir','yemeksepeti','migros','trendyol'])) {
            switch ($key) {
                case 'yemeksepeti':

                    break;
                case 'getir':
                    // Getir güncelleme işlemi
                    break;
                case 'migros':
                    // Migros güncelleme işlemi
                    break;
                case 'trendyol':
                    // Trendyol güncelleme işlemi
                    break;
            }
        }
    }

    $restaurant->update($data);

    return \App\Helpers\JsonResponse::success(
        'Restaurant başarıyla güncellendi',
        $restaurant
    );
});


Route::delete('/restaurants/{id}', function ($id) {
    Restaurant::findOrFail($id)->delete();
    return response()->json(['message' => 'deleted']);
});
