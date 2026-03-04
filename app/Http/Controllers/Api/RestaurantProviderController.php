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
        // 1. Temel Verileri Çek
        $restaurant = Restaurant::where('restaurant_id', $restaurantId)->first();
        $provider = Provider::find($providerId);

        if (!$restaurant || !$provider) {
            return JsonResponse::error((!$restaurant ? 'Restaurant' : 'Provider') . ' not found');
        }

        // 2. Servis Hazırlığı
        RestaurantProviderService::setToken($restaurant->business->token);

        // 3. Mevcut Kayıt Kontrolü ve Servis Çağrısı
        $exists = RestaurantProvider::where('restaurant_id', $restaurant->id)
            ->where('provider_id', $providerId)
            ->exists();

        if (!$exists) {
            $response = RestaurantProviderService::newRestaurantProvider($restaurant->restaurant_id, $provider->provider_id, $request->all());
            Log::info('Restaurant Provider Create Response', (array)json_encode($response));
        }

        // Kayıt yoksa ve oluşturma başarısızsa VEYA kayıt zaten varsa: GÜNCELLEME yap
        if (($exists) || (isset($response) && !$response->success)) {
            $response = RestaurantProviderService::updateRestaurantProvider($restaurant->restaurant_id, $provider->provider_id, $request->all());
            Log::info('Restaurant Provider Update Response', (array)json_encode($response));

            // Update response yapısı farklı olduğu için veriyi standardize ediyoruz
            if (!($response->detail->status ?? false)) {
                return JsonResponse::error('Provider could not be processed');
            }
            $providerData = $response->detail->integrations[0];
        } else {
            // Yeni oluşturma başarılıysa veriyi buradan al
            $providerData = $response->data;
        }

        // 4. Veritabanı İşlemi (UpdateOrCreate ile Tekilleştirme)
        $rp = RestaurantProvider::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'provider_id'   => $providerId,
            ],
            [
                'name'             => $providerData->name,
                'slug'             => $providerData->slug,
                'status'           => $providerData->status,
                'is_eco_friendly'  => $providerData->isEcoFriendly,
                'do_not_knock'     => $providerData->doNotKnock,
                'drop_off_at_door' => $providerData->dropOffAtDoor,
                'auto_approve'     => $providerData->otomatikOnay,
                'service'          => $providerData->service,
                'information'      => json_encode($providerData->information),
            ]
        );

        return JsonResponse::success('İşlem Başarıyla Tamamlandı', $rp, 201);
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
