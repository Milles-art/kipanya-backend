<?php

namespace App\Enums\Auth;

enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
}
