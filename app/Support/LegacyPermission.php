<?php

namespace App\Support;

/**
 * 旧权限码解析工具。
 */
class LegacyPermission
{
    public static function resolveByRouteName(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $map = LegacyRouteMap::routeNameToPermission();
        return $map[$routeName] ?? null;
    }
}
