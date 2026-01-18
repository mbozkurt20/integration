<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use App\Models\Restaurant;
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
        Log::info('Webhook gelen data', $request->all());

        $orderData = $request->all();

        $restaurant = Restaurant::where('restaurant_id',$orderData['restaurantId'])->first();
        $provider = Provider::where('provider_id',$orderData['providerId'])->first();

        $order = Order::create([
            'pid'           => $orderData['pid'] ?? null,
            'restaurant_id' => $restaurant->id ?? null,
            'provider_id'   => $provider->id ?? null,
            'order_id'      => $orderData['_id'] ?? null,
            'shortCode'     => $orderData['shortCode'] ?? null,
            'status'        => $orderData['status'] ?? null,
            'data'          => json_encode($orderData),
        ]);

        $provider = Provider::find($orderData['providerId']);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(
            "{$restaurant->website}/entegra/add-order",
            $orderData
        );

        return $response;
    }

    /*
     * İptal edilen siparişleri kaydeden webhook
     */
    function cancel(Request $request)
    {
        $order =  Order::create([
            'data' => json_encode($request->all()),
            'status' => 'cancel'
        ]);

        $orderData = $request->all();
        $restaurant = Restaurant::where('restaurant_id',$orderData['restaurantId'])->first();
        $provider = Provider::where('provider_id',$orderData['providerId'])->first();

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post(
            "{$restaurant->website}/entegra/cancel-order",
            $orderData
        );

        return $response;
    }
}
