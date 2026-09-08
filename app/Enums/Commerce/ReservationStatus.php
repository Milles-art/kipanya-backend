<?php

namespace App\Enums\Commerce;

enum ReservationStatus: string
{
    case Active = 'active';
    case Fulfilled = 'fulfilled';
    case Released = 'released';
    case Expired = 'expired';
}
