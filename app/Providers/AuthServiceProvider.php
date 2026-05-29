<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * 模型到策略的映射
     */
    protected $policies = [
        // \App\Models\Admin\User::class => \App\Policies\UserPolicy::class,
    ];

    /**
     * 注册认证服务
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 定义超级管理员权限
        Gate::before(function ($user, $ability) {
            if ($user->isSuper()) {
                return true;
            }
        });
    }
}
