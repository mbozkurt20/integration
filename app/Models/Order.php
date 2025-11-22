<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use UuidTrait;

    protected $keyType = 'string';
    public $incrementing = false;

   protected $fillable = [
       'data'
   ];
}
