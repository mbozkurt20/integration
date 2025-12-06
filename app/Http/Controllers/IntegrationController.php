<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderPaymentMethod;
use App\Services\IntegrationService;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    // Bu kontroller default veriler için kullanılıyor
    function paymentMethods()
    {
        $providers = IntegrationService::getProvidersWithPaymentMethods();

        Provider::query()->delete();
        ProviderPaymentMethod::query()->delete();

        foreach ($providers as $provider) {
            $pro = Provider::firstOrCreate(
                ['provider_id' => $provider->_id],
                [
                    'name' => $provider->name,
                    'slug' => $provider->slug,
                ]
            );

            foreach ($provider->paymentMethodList as $paymentMethod) {
                // Ödeme yöntemi zaten var mı kontrol et, yoksa ekle
                ProviderPaymentMethod::updateOrCreate(
                    [
                        'provider_id' => $pro->id,
                        'method_id' => $paymentMethod->id
                    ],
                    [
                        'name' => $paymentMethod->name,
                        'uf' => $paymentMethod->uf,
                        'is_payment_online' => $paymentMethod->is_payment_online,
                    ]
                );
            }
        }
    }
}
