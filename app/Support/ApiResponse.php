<?php

namespace App\Support;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

/**
 * 统一响应门面，约束所有 admin API 返回 code/msg/data。
 */
class ApiResponse
{
    public static function success(mixed $data = null, string $msg = 'Success', int $code = 200): JsonResponse
    {
        return ResponseHelper::success($data, $msg, $code);
    }

    public static function fail(string $msg, int $code = 40001, mixed $data = null): JsonResponse
    {
        return ResponseHelper::error($code, $msg, $data);
    }
}
