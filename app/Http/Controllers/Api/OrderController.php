<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderToRestaurant;
use App\Models\Order;
use App\Models\Setting;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $orderData = $request->all();
        $pid = $orderData['pid'] ?? null;

        Log::info("Pid: {$pid}, Gelen Data:  ".json_encode($orderData));

        if (!$pid) {
            return response()->json([
                'success' => false,
                'message' => 'PID missing'
            ], 422);
        }

        $restaurant = DB::table('restaurants')
            ->where('restaurant_id', $orderData['restaurantId'])
            ->select('id', 'website')
            ->first();

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant Not Found'
            ], 404);
        }

        // 🚀 Her şeyi job'a bırak
        SendOrderToRestaurant::dispatch(
            $orderData,
            $restaurant->id,
            $restaurant->website
        );

        // Client beklemez
      return  ['pos_ticket' => $pid ];
    }

    /*
     * İptal edilen siparişleri kaydeden webhook
     */
    public function cancel(Request $request)
    {
        $orderData = $request->all();
        $pid = $orderData['pid'] ?? null;

        // 1. Gerekli ID'leri ve website bilgisini tek bir hamlede SQL ile çek
        $restaurant = DB::table('restaurants')
            ->where('restaurant_id', $orderData['restaurantId'])
            ->select('id', 'website')
            ->first();

        $providerId = DB::table('providers')
            ->where('provider_id', $orderData['providerId'])
            ->value('id');

        // 2. Doğrudan SQL Insert (Eloquent eventleri tetiklenmez, çok daha hızlıdır)
        DB::table('orders')->insert([
            'pid'           => $pid,
            'restaurant_id' => $restaurant->id ?? null,
            'provider_id'   => $providerId,
            'status'        => 'cancel',
            'data'          => json_encode($orderData),
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        // 3. Yanıt sonrası HTTP isteği
        if ($restaurant && !empty($restaurant->website)) {
            dispatch(function () use ($restaurant, $orderData) {
                try {
                    Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post("{$restaurant->website}/entegra/cancel-order", $orderData);
                } catch (\Exception $e) {
                    Log::error("İptal iletilemedi: " . $e->getMessage());
                }
            })->afterResponse();
        }

        // 4. Kullanıcıya/Servise anında cevap dön
        return ['pos_ticket' => $pid];
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
        $order = Order::where('pid',$orderId)->first();
        if (!$order){
            return response()->json(['success' => false,'message' => 'Sipariş Bulunamadı']);
        }
        if (!$order->restaurant){
            return response()->json(['success' => false,'message' => 'Restaurant Bulunamadı']);
        }
        $entegraBusinessToken = $order->restaurant->business->token;

        OrderService::setToken($entegraBusinessToken);

        $res = OrderService::rejectStatuses($orderId);

        return response()->json(['success' => true,'data' => $res]);
    }

    function reject(Request $request, string $orderId)
    {
        $order = Order::where('pid',$orderId)->first();
        if (!$order){
            return response()->json(['success' => false,'message' => 'Sipariş Bulunamadı']);
        }
        if (!$order->restaurant){
            return response()->json(['success' => false,'message' => 'Restaurant Bulunamadı']);
        }

        $entegraBusinessToken =$order->restaurant->business->token;

        OrderService::setToken($entegraBusinessToken);

        $res = OrderService::reject($orderId,$request->all());

        return response()->json(['success' => true,'data' => $res]);
    }

    function updateStatus(string $orderId)
    {
        $entegraMasterToken = Setting::first()->entegra_master_token;

        OrderService::setToken($entegraMasterToken);

        $res = OrderService::statusUpdate($orderId);

        return $res;
    }
}
