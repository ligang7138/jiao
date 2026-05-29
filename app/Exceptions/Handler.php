<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Session\TokenMismatchException;
use Throwable;
use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;

/**
 * 统一异常处理器
 */
class Handler extends ExceptionHandler
{
    /**
     * 不报告的异常类型
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
        BusinessException::class,
        PermissionException::class,
    ];

    /**
     * 判断是否应该返回 JSON 响应
     */
    protected function shouldReturnJson($request, Throwable $e)
    {
        // API 请求始终返回 JSON
        if ($request->is('api/*')) {
            return true;
        }

        return parent::shouldReturnJson($request, $e);
    }

    /**
     * 处理未认证异常
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '请先登录');
    }

    /**
     * 渲染异常
     */
    public function render($request, Throwable $e)
    {
        // API 请求统一返回 JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    /**
     * 处理 API 异常
     */
    private function handleApiException(Throwable $e)
    {
        // 业务异常
        if ($e instanceof BusinessException) {
            return ResponseHelper::error($e->getCode(), $e->getMessage());
        }

        // 权限异常
        if ($e instanceof PermissionException) {
            return ResponseHelper::error($e->getCode(), $e->getMessage());
        }

        // 验证异常
        if ($e instanceof ValidationException) {
            return ResponseHelper::error(
                ErrorCode::VALIDATION_ERROR,
                $e->getMessage(),
                $e->errors()
            );
        }

        // 认证异常
        if ($e instanceof AuthenticationException) {
            return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '请先登录');
        }

        // 模型未找到
        if ($e instanceof ModelNotFoundException) {
            return ResponseHelper::error(ErrorCode::NOT_FOUND, '数据不存在');
        }

        // 路由未找到
        if ($e instanceof NotFoundHttpException) {
            return ResponseHelper::error(ErrorCode::PAGE_NOT_FOUND, '页面不存在');
        }

        // CSRF（JWT API 不应出现，兜底提示）
        if ($e instanceof TokenMismatchException) {
            return ResponseHelper::error(ErrorCode::FORBIDDEN, '请求无效，请刷新页面后重试');
        }

        // HTTP 异常
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $errorCode = match ($statusCode) {
                401 => ErrorCode::UNAUTHORIZED,
                403 => ErrorCode::FORBIDDEN,
                404 => ErrorCode::PAGE_NOT_FOUND,
                405 => ErrorCode::METHOD_NOT_ALLOWED,
                default => ErrorCode::INTERNAL_ERROR,
            };

            return ResponseHelper::error($errorCode, $e->getMessage());
        }

        // 其他异常（调试模式显示详细信息）
        if (config('app.debug')) {
            return ResponseHelper::error(
                ErrorCode::INTERNAL_ERROR,
                $e->getMessage(),
                [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->take(10)->toArray(),
                ]
            );
        }

        return ResponseHelper::error(ErrorCode::INTERNAL_ERROR, '服务器内部错误');
    }
}