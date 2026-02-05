<?php

use App\Models\Restaurant;
use App\Services\RestaurantService;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://integration.emisoft.com.tr/api/v1/order',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '    {
        "pid": "698485480cf5e1cf680e0456",
        "shortCode": "c331",
        "status": 400,
        "isScheduled": false,
        "scheduledDate": null,
        "confirmationId": "c331",
        "client": {
            "id": "60304c1245d0650006bb9788",
            "name": "Mustafa Y.",
            "location": {
                "lat": "37.165445501663",
                "lon": "28.384714275599",
                "text": "37.165445501663 28.384714275599"
            },
            "clientPhoneNumber": "+90 (850) 346-9382 / 119489",
            "contactPhoneNumber": "+90 (850) 215-1500",
            "deliveryAddress": {
                "id": null,
                "address": "Kötekli - Köstekli, Sıtkı Koçman Caddesi, Bina No: 13, Kat: 3, Daire No: 3, Menteşe, Muğla, ",
                "aptNo": "13",
                "floor": "3",
                "doorNo": "3",
                "city": null,
                "district": "Menteşe",
                "company": null,
                "street": "",
                "building": null,
                "structure": null,
                "room": null,
                "entrance": null,
                "number": null,
                "deliveryMainArea": null,
                "description": "ilkokul arkası filiz apt. yanı"
            }
        },
        "courier": {
            "id": null,
            "status": 200,
            "name": "RestoranKuryesi",
            "location": {
                "lat": "40.740522",
                "lon": "28.623905",
                "text": "40.740522 28.623905"
            }
        },
        "products": [
            {
                "id": "698484f24e3c4c95bfb891fa",
                "count": 1,
                "product": "6973451eceac63ceacab44b4",
                "note": "test sipariş",
                "name": {
                    "tr": "Eko Tavuk Dürüm (50 g)",
                    "en": "Eko Tavuk Dürüm (50 g)"
                },
                "price": 140,
                "optionPrice": 0,
                "priceWithOption": 140,
                "totalPrice": 140,
                "totalOptionPrice": 0,
                "totalPriceWithOption": 140,
                "optionCategories": [],
                "displayInfo": {
                    "title": {
                        "tr": "Eko Tavuk Dürüm (50 g)",
                        "en": "Eko Tavuk Dürüm (50 g)"
                    },
                    "options": {
                        "tr": [],
                        "en": []
                    }
                },
                "removedIngredients": null,
                "removedIngredientsV2": null,
                "extraIngredients": null
            }
        ],
        "clientNote": null,
        "totalPrice": 140,
        "totalDiscountedPrice": 140,
        "deliveryType": 2,
        "doNotKnock": false,
        "isEcoFriendly": false  ,
        "restaurant": {
            "id": "698482b0d8e3a82f800d5bf4",
            "name": "Bİ DÖNER"
        },
        "restaurantName": "Bİ DÖNER",
        "restaurantId": "698482b0d8e3a82f800d5bf4",
        "paymentMethod": 3,
        "posPaymentMethod": "",
        "paymentMethodText": {
            "en": "On Delivery Credit Card Payment",
            "tr": "Kapıda Kredi & Banka Kartı ile Ödeme"
        },
        "pos_ticket": null,
        "isConfirm": null,
        "totalDiscount": 0,
        "dropOffAtDoor": null,
        "created_5min": 0,
        "provider": {
            "slug": "getir",
            "kaynak": "Getir Yemek",
            "id": "60cdef41451ac719569864f3",
            "alici": "getirwh"
        },
        "organizationToken": "698325d7af34c6664f09ac5c|gfgBV1o59wI8sDueyERXBBoCFXmcbOnDzzn2yKmp0a474e32",
        "providerId": "64e8dd5a197634fd59a8b302"
    }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'x-api-key: 5TdR+4H4bE/nGcr2Xt1rR0vYzEMAP8hS5mNZ9rae4Ng='
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
});

Route::get('/restaurants', function () {
    return Restaurant::all();
});

Route::get('/restaurants/{id}', function ($id) {
    $restaurant = Restaurant::find($id);
    return view('restaurants.edit',compact('restaurant'));
});

Route::post('/restaurants', function (Request $request) {
    $restaurant = Restaurant::create($request->only(['name', 'website']));

    if ($restaurant){
        RestaurantService::setToken(config('integration.company_token'));
        RestaurantService::newRestaurant($request->only(['name']));
    }

    return $restaurant;
});

Route::put('/restaurants/{id}', function (Request $request, $id) {
    $restaurant = Restaurant::findOrFail($id);

    $data = $request->only([
        'name',
        'website',
        'getir',
        'yemeksepeti',
        'migros',
        'trendyol'
    ]);

    foreach ($data as $key => $value) {
        if (in_array($key, ['getir','yemeksepeti','migros','trendyol'])) {
            switch ($key) {
                case 'yemeksepeti':

                    break;
                case 'getir':
                    // Getir güncelleme işlemi
                    break;
                case 'migros':
                    // Migros güncelleme işlemi
                    break;
                case 'trendyol':
                    // Trendyol güncelleme işlemi
                    break;
            }
        }
    }

    $restaurant->update($data);

    return \App\Helpers\JsonResponse::success(
        'Restaurant başarıyla güncellendi',
        $restaurant
    );
});


Route::delete('/restaurants/{id}', function ($id) {
    Restaurant::findOrFail($id)->delete();
    return response()->json(['message' => 'deleted']);
});
