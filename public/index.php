<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 注册自动加载器
require __DIR__.'/../vendor/autoload.php';

// 启动应用
$app = require_once __DIR__.'/../bootstrap/app.php';

// 处理请求
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);