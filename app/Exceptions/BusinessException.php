<?php

namespace App\Exceptions;

use Exception;

/**
 * 业务异常类
 * 用于处理业务逻辑中的错误
 */
class BusinessException extends Exception
{
    protected int $errorCode;

    public function __construct(int $errorCode, string $message = '', array $context = [])
    {
        $this->errorCode = $errorCode;

        $message = $message ?: \App\Constants\ErrorCode::getMessage($errorCode);

        parent::__construct($message, $errorCode);
    }

    /**
     * 获取错误码
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 快捷创建方法
     */
    public static function make(int $errorCode, string $message = ''): self
    {
        return new self($errorCode, $message);
    }

    /**
     * 参数验证失败
     */
    public static function validationError(string $message = ''): self
    {
        return new self(\App\Constants\ErrorCode::VALIDATION_ERROR, $message);
    }

    /**
     * 数据不存在
     */
    public static function notFound(string $message = ''): self
    {
        return new self(\App\Constants\ErrorCode::NOT_FOUND, $message);
    }

    /**
     * 业务条件不满足
     */
    public static function conditionNotMet(string $message = ''): self
    {
        return new self(\App\Constants\ErrorCode::BUSINESS_CONDITION_NOT_MET, $message);
    }

    /**
     * 业务限制
     */
    public static function businessLimit(string $message = ''): self
    {
        return new self(\App\Constants\ErrorCode::BUSINESS_LIMIT, $message);
    }

    /**
     * 数据已存在
     */
    public static function duplicateData(string $message = ''): self
    {
        return new self(\App\Constants\ErrorCode::DUPLICATE_DATA, $message);
    }
}