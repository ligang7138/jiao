<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * 控制器基类
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * 成功响应
     */
    protected function success(mixed $data = null, string $message = '操作成功')
    {
        return \App\Helpers\ResponseHelper::success($data, $message);
    }

    /**
     * 错误响应
     */
    protected function error(int $code, string $message = '', mixed $data = null)
    {
        return \App\Helpers\ResponseHelper::error($code, $message, $data);
    }

    /**
     * 分页响应
     */
    protected function paginate($paginator, array $extra = [])
    {
        return \App\Helpers\ResponseHelper::paginate($paginator, $extra);
    }

    /**
     * 获取当前登录用户
     */
    protected function user()
    {
        return auth('jwt')->user();
    }

    /**
     * 获取当前用户ID
     */
    protected function userId(): int
    {
        return auth('jwt')->id();
    }
}
