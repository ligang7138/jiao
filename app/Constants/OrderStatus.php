<?php

namespace App\Constants;

/**
 * 订单状态常量
 */
class OrderStatus
{
    // 订单状态
    public const CANCELLED = 10;      // 已取消
    public const PLACED = 20;         // 已下单
    public const ALLOCATED = 30;      // 已配货
    public const SHIPPED = 40;        // 已发货
    public const RECEIVED = 50;       // 已收货
    public const COMPLETED = 60;      // 已完成

    /**
     * 获取所有状态
     */
    public static function all(): array
    {
        return [
            self::CANCELLED => '已取消',
            self::PLACED => '已下单',
            self::ALLOCATED => '已配货',
            self::SHIPPED => '已发货',
            self::RECEIVED => '已收货',
            self::COMPLETED => '已完成',
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
     * 判断是否可取消
     */
    public static function canCancel(int $status): bool
    {
        return in_array($status, [self::PLACED, self::ALLOCATED]);
    }

    /**
     * 判断是否可发货
     */
    public static function canShip(int $status): bool
    {
        return in_array($status, [self::PLACED, self::ALLOCATED]);
    }

    /**
     * 判断是否可收货
     */
    public static function canReceive(int $status): bool
    {
        return $status === self::SHIPPED;
    }

    /**
     * 判断是否可退货
     */
    public static function canReturn(int $status): bool
    {
        return $status >= self::SHIPPED;
    }
}
