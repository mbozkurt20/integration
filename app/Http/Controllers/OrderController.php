<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Bu controller order webhooks için kullanılıyor
class OrderController extends Controller
{
    /*
  * Gelen siparişleri kaydeden webhook
  */
    function store(Request $request)
    {
        Log::info('gelen data' ,$request->all());
        $order =  Order::create([
            'data' => json_encode($request->all())
        ]);

        return response()->json(['success' => true, 'pos_ticket' => $order->id]);
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

        return response()->json(['success' => true, 'pos_ticket' => $order->id]);
    }
}
