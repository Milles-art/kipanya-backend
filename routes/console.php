<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kipanya:expire-stock-reservations')->everyMinute();
