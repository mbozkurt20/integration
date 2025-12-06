<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Services\RestaurantService;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    function index()
    {
        $restaurants = RestaurantService::restaurants();
        return JsonResponse::success($restaurants);
    }
    /**
     * @param Request $request
     */
    function store(Request $request)
    {
        $restaurant = RestaurantService::newRestaurant($request->all());
        return JsonResponse::success($restaurant);
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
        return JsonResponse::success($restaurant);
    }
}
