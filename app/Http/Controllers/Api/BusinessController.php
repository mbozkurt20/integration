<?php

namespace App\Http\Controllers\Api;

use App\Helpers\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BusinessRequest;
use App\Models\Business;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Services\BusinessService;
use App\Services\RestaurantService;

class BusinessController extends Controller
{
    function index()
    {
        $business = BusinessService::list();
        return JsonResponse::success('İşletmeler Listesi', $business);
    }

    function store(BusinessRequest $request)
    {
        $data = $request->validated();

        $entegraMasterToken = Setting::first()->entegra_master_token;

        BusinessService::setToken($entegraMasterToken);
        $business = BusinessService::newBusiness($data);

        if (!$business){
            return JsonResponse::error('İşletme Oluşturulamadı.');
        }

        if ($business->success) {
           $business = Business::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'user_id' => $business->user_id,
                'token' => $business->token,
            ]);
        }

        return JsonResponse::success('İşletme Eklendi', $business);
    }
}
