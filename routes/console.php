<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kipanya:expire-stock-reservations')->everyMinute();
Schedule::command('kipanya:prune-expired-guest-carts')->daily();
Schedule::command('kipanya:reconcile-payments')->everyTwoMinutes()->withoutOverlapping();
