<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantProvider extends Model
{
   use HasFactory;

   protected $fillable = [
       'restaurant_id',
       'provider_id',
       'name',
       'slug',
       'information',
       'status',
       'is_eco_friendly',
       'do_not_knock',
       'drop_off_at_door',
       'auto_approve',
       'service',
   ];
}
