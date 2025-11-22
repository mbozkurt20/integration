<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class EntegraController extends Controller
{
    /*
     * Order create function
     */
   static function store(Request $request)
   {
     $order =  Order::create([
           'data' => json_encode($request->all())
       ]);

     return response()->json(['success' => true, 'pos_ticket' => $order->id]);
   }

    static function cancel(Request $request)
    {
        $order =  Order::create([
            'data' => json_encode($request->all()),
            'status' => 'cancel'
        ]);

        return response()->json(['success' => true, 'pos_ticket' => $order->id]);
    }
}
