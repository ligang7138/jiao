<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * 事件监听器映射
     */
    protected $listen = [
        \Illuminate\Auth\Events\Registered::class => [
            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * 注册事件
     */
    public function boot(): void
    {
        //
    }

    /**
     * 判断是否自动发现事件
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
