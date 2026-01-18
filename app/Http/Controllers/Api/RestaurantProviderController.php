<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\RestaurantRequest;
use App\Models\Business;
use App\Models\Provider;
use App\Models\Restaurant;
use App\Models\RestaurantProvider;
use App\Services\RestaurantProviderService;
use App\Services\RestaurantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RestaurantProviderController extends Controller
{
    function index()
    {
        $restaurants = RestaurantService::restaurants();
        return JsonResponse::success('İşletmeye ait restaurantlar', $restaurants);
    }


    function store(Request $request, string $restaurantId, int $providerId)
    {
        $restaurant = Restaurant::where('restaurant_id',$restaurantId)->first();

        if (!$restaurant) {
            return JsonResponse::error('Restaurant not found');
        }

        $provider = Provider::find($providerId);
        if (!$provider) {
            return JsonResponse::error('Provider not found');
        }

        $token = $restaurant->business->token;
        RestaurantProviderService::setToken($token);

        $restaurantId = $restaurant->id;

        if (!RestaurantProvider::where('restaurant_id', $restaurantId)->where('provider_id', $providerId)->exists()) {
            $response = RestaurantProviderService::newRestaurantProvider($restaurant->restaurant_id, $provider->provider_id, $request->all());

            if ($response->success) {
                $response = $response->data;

                RestaurantProvider::create([
                    'restaurant_id' => $restaurantId,
                    'provider_id' => $providerId,
                    'name' => $response->name,
                    'slug' => $response->slug,
                    'status' => $response->status,
                    'is_eco_friendly' => $response->isEcoFriendly,
                    'do_not_knock' => $response->doNotKnock,
                    'drop_off_at_door' => $response->dropOffAtDoor,
                    'auto_approve' => $response->otomatikOnay,
                    'service' => $response->service,
                    'information' => json_encode($response->information),
                ]);

                return JsonResponse::success('Restaurant Başarıyla Eklendi', $restaurant, 201);
            } else {
                return JsonResponse::error('Provider not created');
            }
        } else {
            $response = RestaurantProviderService::updateRestaurantProvider($restaurant->restaurant_id, $provider->provider_id, $request->all());

            if ($response['detail']['status']) {
                $response = $response['detail']['integrations'][0];

                RestaurantProvider::where('restaurant_id', $restaurantId)->where('provider_id', $providerId)->update([
                    'restaurant_id' => $restaurantId,
                    'provider_id' => $providerId,
                    'status' => $response['status'],
                    'is_eco_friendly' => $response['isEcoFriendly'],
                    'do_not_knock' => $response['doNotKnock'],
                    'drop_off_at_door' => $response['dropOffAtDoor'],
                    'auto_approve' => $response['otomatikOnay'],
                    'service' => $response['service'],
                    'information' => json_encode($response['information']),
                ]);

                return ['success' => true,'data' => $restaurant];
            } else {
                return JsonResponse::error('Provider not updated');
            }
        }
    }

    /**
     * @param Request $request
     * @param $restaurantId
     */
    function update(Request $request, $restaurantId)
    {
        $restaurant = RestaurantService::updateRestaurant($request->all(), $restaurantId);
        return JsonResponse::success($restaurant, 201);
    }

    /**
     * @param $restaurantId
     */
    function show($restaurantId)
    {
        $restaurant = RestaurantService::showRestaurant($restaurantId);
        return JsonResponse::success('Restaurant Bilgisi', $restaurant);
    }
}
