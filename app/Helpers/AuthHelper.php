<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin\User;

/**
 * 认证辅助类
 * 处理密码加密、验证等认证相关逻辑
 */
class AuthHelper
{
    /**
     * 默认密码（与老系统一致）
     */
    public const DEFAULT_PASSWORD = 'Dxdzcg888';

    /**
     * 使用老系统方式加密密码
     * 老系统: md5(md5(password) + salt)
     */
    public static function encryptLegacyPassword(string $password, string $salt): string
    {
        return md5(md5($password) . $salt);
    }

    /**
     * 生成盐值
     */
    public static function generateSalt(): string
    {
        return substr(md5((string) time()), 0, 10);
    }

    /**
     * 验证老系统密码
     */
    public static function verifyLegacyPassword(string $password, string $hash, string $salt): bool
    {
        $legacyHash = self::encryptLegacyPassword($password, $salt);
        return $legacyHash === $hash;
    }

    /**
     * 使用 Laravel 方式加密密码（bcrypt）
     */
    public static function encryptPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * 是否为 Laravel 支持的现代哈希（bcrypt / argon2）
     */
    public static function isModernPasswordHash(string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        return str_starts_with($hash, '$2y$')
            || str_starts_with($hash, '$2a$')
            || str_starts_with($hash, '$2b$')
            || str_starts_with($hash, '$argon2i$')
            || str_starts_with($hash, '$argon2id$');
    }

    /**
     * 验证密码（兼容新老两种加密方式）
     */
    public static function verifyPassword(string $password, string $hash, string $salt = ''): bool
    {
        if ($hash === '') {
            return false;
        }

        // 老库为 md5(md5(password)+salt)，不能对非 bcrypt 哈希调用 Hash::check（会抛异常）
        if (!self::isModernPasswordHash($hash)) {
            return $salt !== '' && self::verifyLegacyPassword($password, $hash, $salt);
        }

        return Hash::check($password, $hash);
    }

    /**
     * 当前 user 表 password 字段是否可存 bcrypt（旧库多为 varchar(32)）
     */
    public static function supportsModernPasswordStorage(): bool
    {
        static $supported = null;
        if ($supported !== null) {
            return $supported;
        }

        $table = config('legacy.auth.user_table', 'user');
        $row = DB::selectOne(
            'SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, 'password']
        );

        $supported = $row && (int) $row->len >= 60;

        return $supported;
    }

    /**
     * 升级密码加密方式（从老系统迁移到新系统）
     */
    public static function upgradePassword(User $user, string $password): void
    {
        if (!self::supportsModernPasswordStorage()) {
            return;
        }

        $user->password = self::encryptPassword($password);
        $user->salt = '';
        $user->save();
    }

    /**
     * 重置密码为默认密码
     */
    public static function resetToDefaultPassword(User $user): void
    {
        if (self::supportsModernPasswordStorage()) {
            $user->password = self::encryptPassword(self::DEFAULT_PASSWORD);
            $user->salt = '';
            $user->save();

            return;
        }

        $salt = self::generateSalt();
        $user->password = self::encryptLegacyPassword(self::DEFAULT_PASSWORD, $salt);
        $user->salt = $salt;
        $user->save();
    }
}