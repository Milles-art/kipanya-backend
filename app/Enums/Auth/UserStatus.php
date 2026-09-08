<?php

namespace App\Enums\Auth;

enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Pending = 'pending';
}
