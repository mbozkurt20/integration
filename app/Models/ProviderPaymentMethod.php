<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderPaymentMethod extends Model
{
    protected $fillable = ['provider_id', 'uf', 'is_payment_online','name','method_id'];
}
