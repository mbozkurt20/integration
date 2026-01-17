<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

// Bu controller order webhooks için kullanılıyor
class OrderController extends Controller
{
    public string $endpoint;

    public function __construct()
    {
        $this->endpoint = 'http://127.0.0.1/';
    }

    /*
  * Gelen siparişleri kaydeden webhook
  */
    public function store(Request $request)
    {
        Log::info('Webhook gelen data', $request->all());

        $orderData = $request->all();

        $order = Order::create([
            'pid'           => $orderData['pid'] ?? null,
            'restaurant_id' => $orderData['restaurantId'] ?? null,
            'provider_id'   => $orderData['providerId'] ?? null,
            'order_id'      => $orderData['_id'] ?? null,
            'shortCode'     => $orderData['shortCode'] ?? null,
            'status'        => $orderData['status'] ?? null,
            'data'          => json_encode($orderData),
        ]);

        $provider = Provider::find($orderData['providerId']);
        // 👉 Token YOK
        $response = Http::post("{$this->endpoint}/entegra/add-order".$provider->name, [
            'pid'           => $order->pid,
            'restaurant_id' => $order->restaurant_id,
            'provider_id'   => $order->provider_id,
            'order_id'      => $order->order_id,
            'shortCode'     => $order->shortCode,
            'status'        => $order->status,
            'data'          => $orderData,
        ]);

        Log::info('Giden webhook response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        return response()->json([
            'success'    => true,
            'pos_ticket' => $order->id
        ]);
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

        $response = Http::post("{$this->endpoint}/entegra/cancel-order", [
            'pid'           => $order->pid,
            'restaurant_id' => $order->restaurant_id,
            'provider_id'   => $order->provider_id,
            'order_id'      => $order->order_id,
            'shortCode'     => $order->shortCode,
            'status'        => $order->status,
            'data'          => $orderData,
        ]);

        return response()->json(['success' => true, 'pos_ticket' => $order->id]);
    }
}
