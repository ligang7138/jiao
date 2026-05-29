<?php

namespace App\Exceptions;

use App\Constants\ErrorCode;

/**
 * 权限异常类
 */
class PermissionException extends BusinessException
{
    public function __construct(string $message = '无权限执行此操作')
    {
        parent::__construct(ErrorCode::PERMISSION_DENIED, $message);
    }

    /**
     * 快捷创建
     */
    public static function denied(string $message = ''): self
    {
        return new self($message ?: '权限不足');
    }

    /**
     * 需要登录
     */
    public static function loginRequired(): self
    {
        return new self('请先登录');
    }
}