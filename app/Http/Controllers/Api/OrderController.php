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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Bu controller order webhooks için kullanılıyor
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $orderData = $request->all();
        $pid = $orderData['pid'] ?? null;

        // 1. Hızlıca Restoran ve Provider ID'lerini al
        $restaurant = DB::table('restaurants')
            ->where('restaurant_id', $orderData['restaurantId'])
            ->select('id', 'website')
            ->first();

        if (!$restaurant) {
            return response()->json(['success' => false, 'message' => 'Restaurant Not Found'], 404);
        }

        // 2. Mükerrer kontrolü ve Kayıt (Eloquent olmadan)
        $exists = DB::table('orders')->where('pid', $pid)->exists();

        if (!$exists) {
            $providerId = DB::table('providers')
                ->where('provider_id', $orderData['providerId'])
                ->value('id');

            DB::table('orders')->insert([
                'id' => Str::uuid(),
                'order_id'      => $orderData['_id'] ?? $orderData['id'] ?? $pid,
                'pid'           => $pid,
                'restaurant_id' => $restaurant->id,
                'provider_id'   => $providerId,
                'shortCode'     => $orderData['shortCode'] ?? null,
                'status'        => $orderData['status'] ?? null,
                'data'          => json_encode($orderData),
                'created_at'    => now(), // Query Builder'da elle eklemelisin
                'updated_at'    => now(),
            ]);

            // 3. Arka planda HTTP isteği
            dispatch(function () use ($restaurant, $orderData) {
                Http::post("{$restaurant->website}/entegra/add-order", $orderData);
            })->afterResponse();
        }

        return ['pos_ticket' => $pid];
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
