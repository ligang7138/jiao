<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * 编码生成辅助类
 * 处理各类业务编码的生成
 */
class CodeHelper
{
    /**
     * 生成商品编码
     * 特殊分类(>=10)时共用编号，普通分类时各分类独立编号
     */
    public static function generateGoodsSn(int $cateId): string
    {
        // 查询最大编号的逻辑在 Service 中处理
        // 这里只提供格式化方法
        return '';
    }

    /**
     * 格式化商品编码（补齐6位）
     */
    public static function formatGoodsSn(int $number): string
    {
        return str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * 生成供应商编码
     * 编码 = 10000 + 自增ID
     */
    public static function generateSupplierCode(int $id): string
    {
        return (string) (10000 + $id);
    }

    /**
     * 生成学校编码
     */
    public static function generateSchoolSn(int $id): string
    {
        return (string) (10000 + $id);
    }

    /**
     * 生成订单编号
     * 格式: O + 日期时间 + 随机3位
     */
    public static function generateOrderSn(): string
    {
        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);

        return 'O' . date('YmdHis') . $milliseconds . Str::random(3);
    }

    /**
     * 生成退单编号
     * 格式: B + 日期时间 + 随机3位
     */
    public static function generateBackorderSn(): string
    {
        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);

        return 'B' . date('YmdHis') . $milliseconds . Str::random(3);
    }

    /**
     * 生成对账单编号
     * 格式: R + 日期时间 + 随机3位 + 最后一位标识
     */
    public static function generateReceiptSn(string $suffix = ''): string
    {
        $microtime = microtime(true);
        $milliseconds = sprintf('%03d', ($microtime - floor($microtime)) * 1000);
        $suffixChar = $suffix ? substr($suffix, -1) : Str::random(1);

        return 'R' . date('ymdHis') . $milliseconds . $suffixChar;
    }

    /**
     * 生成唯一 Token
     */
    public static function generateToken(): string
    {
        return md5(uniqid((string) mt_rand(), true)) . Str::random(16);
    }

    /**
     * 金额转中文大写
     */
    public static function convertAmountToCn(float $amount, bool $includeYuan = true): string
    {
        $cnums = ['零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖'];
        $cunits = ['', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿'];

        // 处理小数部分
        $amountStr = sprintf('%.2f', $amount);
        $parts = explode('.', $amountStr);
        $intPart = (int) $parts[0];
        $decPart = $parts[1];

        $result = '';
        $i = 0;

        while ($intPart > 0) {
            $digit = $intPart % 10;
            $result = $cnums[$digit] . ($digit > 0 ? $cunits[$i] : '') . $result;
            $intPart = (int) ($intPart / 10);
            $i++;
        }

        // 添加元
        if ($includeYuan && !empty($result)) {
            $result .= '元';
        }

        // 处理角分
        if ($decPart !== '00') {
            $jiao = (int) substr($decPart, 0, 1);
            $fen = (int) substr($decPart, 1, 1);

            if ($jiao > 0) {
                $result .= $cnums[$jiao] . '角';
            }
            if ($fen > 0) {
                $result .= $cnums[$fen] . '分';
            }
        } elseif ($includeYuan && !empty($result)) {
            $result .= '整';
        }

        return $result;
    }
}