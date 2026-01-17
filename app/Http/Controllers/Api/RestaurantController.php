<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantRequest;
use App\Models\Business;
use App\Models\Restaurant;
use App\Services\RestaurantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RestaurantController extends Controller
{
    function index()
    {
        $restaurants = Restaurant::all();
        return JsonResponse::success('İşletmeye ait restoranlar', $restaurants);
    }

    function store(RestaurantRequest $request)
    {
        $data = $request->validated();
        $payload = ['name' => $data['name']];

        $business = Business::find($data['businessId']);

        if (!$business) {
            return JsonResponse::error('İşyeri Bulunamadi',404);
        }

        RestaurantService::setToken($business->token);
        $restaurant = RestaurantService::newRestaurant($payload);

        if (!$restaurant){
            return JsonResponse::error('Kayıt Oluşturulamadı');
        }

        if ($restaurant->success) {
            Restaurant::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'restaurant_id' => $restaurant->restaurant_id,
            ]);
        }

        return JsonResponse::success('Restaurant Başarıyla Eklendi',$restaurant,201);
    }

    /**
     * @param Request $request
     * @param $restaurantId
     */
    function update(Request $request, $restaurantId)
    {
        $restaurant = RestaurantService::updateRestaurant($request->all(),$restaurantId);
        return JsonResponse::success($restaurant,201);
    }

    /**
     * @param $restaurantId
     */
    function show($restaurantId)
    {
        $restaurant = RestaurantService::showRestaurant($restaurantId);
        return JsonResponse::success('Restaurant Bilgisi',$restaurant);
    }
}
