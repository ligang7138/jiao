<?php

/**
 * Laravel 启动文件
 */

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // JWT 前后端分离：禁用 Sanctum stateful/session，API 不走 CSRF
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        $middleware->alias([
            'jwt' => \App\Http\Middleware\JwtAuth::class,
            'rbac' => \App\Http\Middleware\RbacPermission::class,
            'request.log' => \App\Http\Middleware\RequestLog::class,
            'rate.limit' => \App\Http\Middleware\RateLimit::class,
            'cross.domain' => \App\Http\Middleware\CrossDomain::class,
        ]);

        // API 路由认证失败时返回 401 JSON 响应
        $middleware->redirectGuestsTo(function (Request $request) {
            // 纯 API 项目，始终返回 null 让中间件返回 401 JSON
            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 处理未认证异常 - API 请求返回 JSON 而不是重定向
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'code' => 401,
                    'msg' => '请先登录',
                    'data' => null,
                ], 401);
            }
        });
    })->create();
