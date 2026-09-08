<?php

namespace App\Enums\Commerce;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
