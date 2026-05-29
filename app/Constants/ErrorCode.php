<?php

namespace App\Constants;

/**
 * 错误码常量
 * 与老系统错误码保持兼容
 */
class ErrorCode
{
    // 成功
    public const SUCCESS = 200;

    // 客户端错误 (400xx)
    public const BAD_REQUEST = 40000;
    public const VALIDATION_ERROR = 40001;
    public const NOT_FOUND = 40002;
    public const BUSINESS_LIMIT = 40003;
    public const BUSINESS_CONDITION_NOT_MET = 40004;
    public const DUPLICATE_DATA = 40005;
    // 与旧系统兼容的关键状态码
    public const LEGACY_PERMISSION_DENIED = 40098;
    public const LEGACY_ACTION_NOT_FOUND = 40099;

    // 认证错误 (401xx)
    public const UNAUTHORIZED = 40100;
    public const TOKEN_EXPIRED = 40101;
    public const TOKEN_INVALID = 40102;
    public const LOGIN_REQUIRED = 40103;

    // 权限错误 (403xx)
    public const FORBIDDEN = 40300;
    public const PERMISSION_DENIED = 40301;

    // 资源错误 (404xx)
    public const RESOURCE_NOT_FOUND = 40400;
    public const PAGE_NOT_FOUND = 40401;

    // 方法错误 (405xx) - 新系统语义码
    public const METHOD_NOT_ALLOWED = 40500;
    public const ACTION_NOT_FOUND = 40501;

    // 服务端错误 (500xx)
    public const INTERNAL_ERROR = 50000;
    public const DATABASE_ERROR = 50001;
    public const CACHE_ERROR = 50002;
    public const FILE_ERROR = 50003;

    // 业务错误 (600xx) - 与老系统对齐
    public const ORDER_STATUS_ERROR = 60001;
    public const GOODS_NOT_PUBLISHABLE = 60002;
    public const SUPPLIER_STATUS_ERROR = 60003;
    public const CANTEEN_STATUS_ERROR = 60004;
    public const BIDDING_STATUS_ERROR = 60005;

    /**
     * 获取错误码对应的错误信息
     */
    public static function getMessage(int $code): string
    {
        return match ($code) {
            self::SUCCESS => '操作成功',
            self::BAD_REQUEST => '请求参数错误',
            self::VALIDATION_ERROR => '参数验证失败',
            self::NOT_FOUND => '数据不存在',
            self::BUSINESS_LIMIT => '业务限制',
            self::BUSINESS_CONDITION_NOT_MET => '业务条件不满足',
            self::DUPLICATE_DATA => '数据已存在',
            self::LEGACY_PERMISSION_DENIED => '对不起，您没有操作权限！',
            self::LEGACY_ACTION_NOT_FOUND => '方法不存在',
            self::UNAUTHORIZED => '未授权',
            self::TOKEN_EXPIRED => 'Token已过期',
            self::TOKEN_INVALID => 'Token无效',
            self::LOGIN_REQUIRED => '请先登录',
            self::FORBIDDEN => '禁止访问',
            self::PERMISSION_DENIED => '权限不足',
            self::RESOURCE_NOT_FOUND => '资源不存在',
            self::PAGE_NOT_FOUND => '页面不存在',
            self::METHOD_NOT_ALLOWED => '方法不允许',
            self::ACTION_NOT_FOUND => '方法不存在',
            self::INTERNAL_ERROR => '服务器内部错误',
            self::DATABASE_ERROR => '数据库操作失败',
            self::CACHE_ERROR => '缓存操作失败',
            self::FILE_ERROR => '文件操作失败',
            self::ORDER_STATUS_ERROR => '订单状态错误',
            self::GOODS_NOT_PUBLISHABLE => '商品不满足上架条件',
            self::SUPPLIER_STATUS_ERROR => '供应商状态错误',
            self::CANTEEN_STATUS_ERROR => '食堂状态错误',
            self::BIDDING_STATUS_ERROR => '合作状态错误',
            default => '未知错误',
        };
    }

    /**
     * 判断是否为成功状态码
     */
    public static function isSuccess(int $code): bool
    {
        return $code === self::SUCCESS;
    }

    /**
     * 获取对应的 HTTP 状态码
     */
    public static function getHttpStatus(int $code): int
    {
        return match (true) {
            $code >= 40000 && $code < 40100 => 400,
            $code >= 40100 && $code < 40300 => 401,
            $code >= 40300 && $code < 40400 => 403,
            $code >= 40400 && $code < 40500 => 404,
            $code >= 50000 => 500,
            default => 200,
        };
    }
}
