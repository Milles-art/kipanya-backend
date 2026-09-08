<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('kipanya:publish-scheduled-cartoons')->everyMinute();
Schedule::command('kipanya:expire-stock-reservations')->everyMinute();
