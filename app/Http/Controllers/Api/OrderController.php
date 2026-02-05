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
        Log::info('Webhook gelen data', $request->all());

        $orderData = $request->all();

        $restaurant = Restaurant::where('restaurant_id',$orderData['restaurantId'])->first();

        if ($restaurant){
            $provider = Provider::where('provider_id',$orderData['providerId'])->first();

            if (!Order::where('pid',$orderData['pid'])->exists()) {
                $order = Order::create([
                    'order_id'      =>  $orderData['_id'] ?? $orderData['id'] ?? $orderData['pid'] ?? null,
                    'pid'           => $orderData['pid'] ?? null,
                    'restaurant_id' => $restaurant->id ?? null,
                    'provider_id'   => $provider->id ?? null,
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
            }

            return ['pos_ticket' => $orderData['pid']];
        }

        return response()->json(['success' => false, 'Restaurant Not Found'],404);
    }

    /*
     * İptal edilen siparişleri kaydeden webhook
     */
    function cancel(Request $request)
    {
         Order::create([
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

        return ['pos_ticket' => $orderData['pid']];
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
