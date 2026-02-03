<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'PENDING';
    case ACCEPT = "ACCEPT";
    case PACKAGED = "PACKAGED";
    case REJECT = "REJECT";
    case DELIVERED = "DELIVERED";
}
