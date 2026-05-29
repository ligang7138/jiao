<?php

namespace App\Support;

/**
 * 旧权限码解析与校验工具。
 */
class LegacyPermission
{
    /**
     * @return string|array<int, string>|null
     */
    public static function resolveByRouteName(?string $routeName): string|array|null
    {
        if (!$routeName) {
            return null;
        }

        $map = LegacyRouteMap::routeNameToPermission();

        return $map[$routeName] ?? null;
    }

    /**
     * 校验用户是否拥有指定旧权限码（支持 OR 关系的多权限配置）。
     */
    public static function userHasPermission(mixed $user, string|array|null $permissionCode): bool
    {
        if ($permissionCode === null) {
            return true;
        }

        if (is_string($permissionCode)) {
            return self::userHasSinglePermission($user, $permissionCode);
        }

        foreach ($permissionCode as $code) {
            if (self::userHasSinglePermission($user, $code)) {
                return true;
            }
        }

        return false;
    }

    private static function userHasSinglePermission(mixed $user, string $permissionCode): bool
    {
        $permissions = $user->privilege ?? $user->permissions ?? [];
        if (!is_array($permissions)) {
            return false;
        }

        if (in_array('*', $permissions, true)) {
            return true;
        }

        if (in_array($permissionCode, $permissions, true)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!is_string($permission) || !str_ends_with($permission, '.*')) {
                continue;
            }

            $prefix = substr($permission, 0, -2);
            if (str_starts_with($permissionCode, $prefix . '.')) {
                return true;
            }
        }

        return false;
    }
}
