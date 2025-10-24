<?php

namespace App\Enum;

enum OrderStatus: string
{
    case IN_PREPARATION = 'in_preparation';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}