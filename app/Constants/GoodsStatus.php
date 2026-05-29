<?php

namespace App\Constants;

/**
 * 商品状态常量
 */
class GoodsStatus
{
    // 商品状态
    public const OFF_SHELF = 0;    // 下架
    public const ON_SHELF = 1;     // 上架

    /**
     * 获取所有状态
     */
    public static function all(): array
    {
        return [
            self::OFF_SHELF => '下架',
            self::ON_SHELF => '上架',
        ];
    }

    /**
     * 获取状态名称
     */
    public static function getName(int $status): string
    {
        return self::all()[$status] ?? '未知状态';
    }

    /**
     * 判断是否可上架
     */
    public static function canPublish(int $status): bool
    {
        return $status === self::OFF_SHELF;
    }

    /**
     * 判断是否可下架
     */
    public static function canUnpublish(int $status): bool
    {
        return $status === self::ON_SHELF;
    }
}
