<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

// Bu controller order webhooks için kullanılıyor
class OrderController extends Controller
{
    /*
  * Gelen siparişleri kaydeden webhook
  */
    public function store(Request $request)
    {
        $orderData = $request->all();
        Log::info('Webhook gelen data', $orderData);

        // 1. Restoran Kontrolü
        $restaurant = Restaurant::where('restaurant_id', $orderData['restaurantId'])->first();

        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant Not Found'], 404);
        }

        // 2. Mükerrer Kayıt Kontrolü ve Kayıt İşlemi
        if (!Order::where('pid', $orderData['pid'])->exists()) {
            try {
                $provider = Provider::where('provider_id', $orderData['providerId'])->first();

                $order = Order::create([
                    'order_id'      => $orderData['_id'] ?? $orderData['id'] ?? $orderData['pid'] ?? null,
                    'pid'           => $orderData['pid'] ?? null,
                    'restaurant_id' => $restaurant->id,
                    'provider_id'   => $provider->id ?? null,
                    'shortCode'     => $orderData['shortCode'] ?? null,
                    'status'        => $orderData['status'] ?? null,
                    'data'          => json_encode($orderData),
                ]);

                // 3. Zaman Alan HTTP İsteğini Yanıt Sonrasına Ertele (Dispatch After Response)
                // Bu kısım kuyruk (queue) ayarı gerektirmez, yanıt dönüldükten hemen sonra çalışır.
                dispatch(function () use ($restaurant, $orderData) {
                    Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post("{$restaurant->website}/entegra/add-order", $orderData);
                })->afterResponse();

            } catch (\Exception $exception) {
                Log::error('Sipariş kayıt hatası: ' . $exception->getMessage());
                return response()->json(['success' => false, 'error' => 'Internal Server Error'], 500);
            }
        }

        // 4. Hızlıca Yanıt Dön
        return response()->json(['pos_ticket' => $orderData['pid']], 200);
    }

    /*
     * İptal edilen siparişleri kaydeden webhook
     */
    public function cancel(Request $request)
    {
        $orderData = $request->all();

        // 1. Veritabanı kaydını anında yapıyoruz (Hız için en başa aldık)
        // Not: restaurant_id ve provider_id alanlarını da eklemek raporlama için iyi olabilir
        $restaurant = Restaurant::where('restaurant_id', $orderData['restaurantId'])->first();
        $provider = Provider::where('provider_id', $orderData['providerId'])->first();

        $order = Order::create([
            'pid'           => $orderData['pid'] ?? null,
            'restaurant_id' => $restaurant->id ?? null,
            'provider_id'   => $provider->id ?? null,
            'status'        => 'cancel',
            'data'          => json_encode($orderData),
        ]);

        // 2. Eğer restoran bulunamadıysa dış siteye istek atmaya gerek yok
        if ($restaurant && !empty($restaurant->website)) {

            // 3. Dış servis isteğini yanıt sonrasına (arka plana) atıyoruz
            dispatch(function () use ($restaurant, $orderData) {
                try {
                    Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post("{$restaurant->website}/entegra/cancel-order", $orderData);
                } catch (\Exception $e) {
                    Log::error("İptal webhook iletilemedi: " . $e->getMessage());
                }
            })->afterResponse();

        }

        // 4. Yanıtı ışık hızında dönüyoruz
        return response()->json(['pos_ticket' => $orderData['pid'] ?? null], 200);
    }

    function confirmation(string $orderId)
    {
        $entegraMasterToken = Setting::first()->entegra_master_token;

        OrderService::setToken($entegraMasterToken);

        $res = OrderService::confirmation($orderId);

        return response()->json(['success' => true,'data' => $res]);
    }

    function rejectStatuses(string $orderId)
    {
        $entegraBusinessToken = Order::where('order_id',$orderId)->first()->restaurant->business->token;

        OrderService::setToken($entegraBusinessToken);

        $res = OrderService::rejectStatuses($orderId);

        return response()->json(['success' => true,'data' => $res]);
    }

    function reject(Request $request, string $orderId)
    {
        $entegraBusinessToken = Order::where('order_id',$orderId)->first()->restaurant->business->token;

        OrderService::setToken($entegraBusinessToken);

        $res = OrderService::reject($orderId,$request->all());

        return response()->json(['success' => true,'data' => $res]);
    }

    function updateStatus(string $orderId)
    {
        $entegraMasterToken = Setting::first()->entegra_master_token;

        OrderService::setToken($entegraMasterToken);

        $res = OrderService::statusUpdate($orderId);

        return response()->json(['success' => true,'data' => $res]);
    }
}
