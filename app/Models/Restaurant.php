<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'restaurant_id',
        'website',
        'token',
        'website_restaurant_id',
    ];

    public function business(){
        return $this->belongsTo(Business::class,'business_id');
    }
}
