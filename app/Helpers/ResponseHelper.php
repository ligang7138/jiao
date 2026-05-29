<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use App\Constants\ErrorCode;

/**
 * API 响应辅助类
 * 提供统一的 API 响应格式
 */
class ResponseHelper
{
    /**
     * 构建统一响应载荷（新系统 + 旧系统兼容字段）
     */
    private static function payload(int $code, string $msg, mixed $data): array
    {
        $payload = [
            // 新系统主字段
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ];

        if (config('legacy.response.include_legacy_fields', true)) {
            // 旧系统兼容字段（迁移期保留）
            $payload['status'] = $code;
            $payload['message'] = $msg;
        }

        return $payload;
    }

    /**
     * 成功响应
     */
    public static function success(mixed $data = null, string $message = '操作成功', int $code = ErrorCode::SUCCESS): JsonResponse
    {
        return response()->json(self::payload($code, $message, $data));
    }

    /**
     * 错误响应
     */
    public static function error(int $code, string $message = '', mixed $data = null): JsonResponse
    {
        $httpStatus = ErrorCode::getHttpStatus($code);
        return response()->json(
            self::payload($code, $message ?: ErrorCode::getMessage($code), $data),
            $httpStatus
        );
    }

    /**
     * 分页响应
     */
    public static function paginate($paginator, array $extra = []): JsonResponse
    {
        return response()->json(self::payload(
            ErrorCode::SUCCESS,
            '操作成功',
            [
                'list' => $paginator->items(),
                'total' => $paginator->total(),
                'page' => $paginator->currentPage(),
                'page_size' => $paginator->perPage(),
                'total_page' => $paginator->lastPage(),
                ...$extra,
            ]
        ));
    }

    /**
     * 无内容响应（用于删除操作）
     */
    public static function noContent(): JsonResponse
    {
        return response()->json(self::payload(ErrorCode::SUCCESS, '操作成功', null))->setStatusCode(204);
    }

    /**
     * 创建成功响应（用于新增操作）
     */
    public static function created(mixed $data = null, string $message = '创建成功'): JsonResponse
    {
        return response()->json(self::payload(ErrorCode::SUCCESS, $message, $data))->setStatusCode(201);
    }
}