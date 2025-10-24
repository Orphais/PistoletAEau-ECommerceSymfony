<?php

namespace App\Enum;

enum ProductStatus: string
{
    case AVAILABLE = 'available';
    case OUT_OF_STOCK = 'out_of_stock';
    case PREORDER = 'preorder';
}