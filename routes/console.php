<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

// 定时任务
Schedule::command('orders:auto-audit')->everyMinute();
Schedule::command('bidding:sync-status')->everyMinute();
Schedule::command('goods:auto-down')->dailyAt('00:03');
Schedule::command('receivable:generate')->dailyAt('00:02');
Schedule::command('jiagewang:sync')->everyTenMinutes();
Schedule::command('canteen:calc-purchase')->everyThirtyMinutes();
