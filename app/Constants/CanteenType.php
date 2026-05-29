<?php

namespace App\Constants;

/**
 * 食堂类型常量
 */
class CanteenType
{
    public const TEACHER = 1;  // 教师食堂
    public const STUDENT = 2;  // 学生食堂

    /**
     * 获取所有类型
     */
    public static function all(): array
    {
        return [
            self::TEACHER => '教师食堂',
            self::STUDENT => '学生食堂',
        ];
    }

    /**
     * 获取类型名称
     */
    public static function getName(int $type): string
    {
        return self::all()[$type] ?? '未知类型';
    }
}
