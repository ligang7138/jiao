<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 按旧后台 main.php / index.php 规则构建左侧菜单。
 */
class LegacyMenuBuilder
{
    public static function buildForUser($user): array
    {
        if (!Schema::hasTable('system_menu')) {
            return [];
        }

        if ($user->isSuper()) {
            return self::buildFullMenu();
        }

        $menuIds = self::collectMenuIdsFromPosts($user);
        $privilegePaths = self::resolvePrivilegePaths($user, $menuIds);
        $allowedModules = self::resolveAllowedModules($menuIds);

        return self::buildMenu($privilegePaths, $allowedModules);
    }

    /**
     * @param array<int, int> $menuIds
     * @return array<int, string>
     */
    public static function resolvePrivilegePaths($user, array $menuIds = []): array
    {
        $attrs = $user->getAttributes();
        $cached = $attrs['privilege'] ?? $attrs['permissions'] ?? null;
        if (is_array($cached) && self::looksLikePaths($cached)) {
            return self::mergePublicPaths($cached);
        }

        if (empty($menuIds)) {
            $menuIds = self::collectMenuIdsFromPosts($user);
        }

        if (empty($menuIds)) {
            return self::publicPaths();
        }

        $idList = implode(',', array_map('intval', $menuIds));

        $paths = DB::table('system_menu')
            ->whereRaw("id in ({$idList})")
            ->pluck('path')
            ->filter()
            ->values()
            ->all();

        return self::mergePublicPaths($paths);
    }

    /**
     * @param array<int, int> $menuIds
     * @return array<int, string>
     */
    public static function resolveAllowedModules(array $menuIds): array
    {
        if (empty($menuIds)) {
            return DB::table('system_menu')
                ->where('status', 0)
                ->where('level', 1)
                ->pluck('module')
                ->unique()
                ->values()
                ->all();
        }

        $idList = implode(',', array_map('intval', $menuIds));

        return DB::table('system_menu')
            ->whereRaw("id in ({$idList})")
            ->pluck('module')
            ->merge(
                DB::table('system_menu')
                    ->where('status', 0)
                    ->where('level', 1)
                    ->pluck('module')
            )
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $privilegePaths
     * @param array<int, string> $allowedModules
     */
    private static function buildMenu(array $privilegePaths, array $allowedModules): array
    {
        $rows = [];

        $modules = DB::table('system_menu')
            ->where('level', 1)
            ->where('status', 1)
            ->orderBy('sort')
            ->get();

        foreach ($modules as $module) {
            if (!in_array($module->module, $allowedModules, true)) {
                continue;
            }

            $children = DB::table('system_menu')
                ->where('pid', $module->id)
                ->where('level', 2)
                ->where('status', 1)
                ->orderBy('sort')
                ->get()
                ->filter(function ($item) use ($privilegePaths) {
                    if (self::isHiddenMenu($item->path)) {
                        return false;
                    }
                    if (empty($item->path)) {
                        return true;
                    }

                    return in_array($item->path, $privilegePaths, true);
                })
                ->map(fn ($item) => self::formatMenuItem($item))
                ->filter(fn ($item) => !empty($item['path']))
                ->values()
                ->all();

            if (empty($children)) {
                continue;
            }

            $rows[] = [
                'id' => $module->id,
                'module' => $module->module,
                'menu' => $children,
            ];
        }

        return $rows;
    }

    /**
     * 重构后隐藏的旧模块菜单 path
     */
    private static function hiddenMenuPaths(): array
    {
        return [
            'jiagewang.index',
            'jiagewang.match',
            'jiagewang.import',
            'jiagewang.history',
        ];
    }

    private static function isHiddenMenu(?string $path): bool
    {
        return $path && in_array($path, self::hiddenMenuPaths(), true);
    }

    private static function buildFullMenu(): array
    {
        $modules = DB::table('system_menu')
            ->where('level', 1)
            ->where('status', 1)
            ->orderBy('sort')
            ->get();

        $rows = [];
        foreach ($modules as $module) {
            $children = DB::table('system_menu')
                ->where('pid', $module->id)
                ->where('level', 2)
                ->where('status', 1)
                ->orderBy('sort')
                ->get()
                ->filter(fn ($item) => !self::isHiddenMenu($item->path))
                ->map(fn ($item) => self::formatMenuItem($item))
                ->filter(fn ($item) => !empty($item['path']))
                ->values()
                ->all();

            if (empty($children)) {
                continue;
            }

            $rows[] = [
                'id' => $module->id,
                'module' => $module->module,
                'menu' => $children,
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, mixed>
     */
    private static function formatMenuItem(object $item): array
    {
        return [
            'id' => $item->id,
            'func' => $item->func,
            'path' => $item->path,
            'legacy_url' => self::legacyUrl($item->path),
            'route' => LegacyMenuPathMap::resolve($item->path),
        ];
    }

    /**
     * @param array<int, mixed> $values
     */
    private static function looksLikePaths(array $values): bool
    {
        foreach ($values as $value) {
            if ($value === '*') {
                continue;
            }
            if (is_string($value) && str_contains($value, '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, int>
     */
    public static function collectMenuIdsForUser($user): array
    {
        return self::collectMenuIdsFromPosts($user);
    }

    /**
     * @return array<int, int>
     */
    private static function collectMenuIdsFromPosts($user): array
    {
        $ids = [];

        if (method_exists($user, 'posts')) {
            $user->loadMissing('posts');
            foreach ($user->posts as $post) {
                if (($post->status ?? 1) != 1) {
                    continue;
                }
                $ids = array_merge($ids, self::parsePrivilegeIds($post->privilege ?? ''));
            }
        }

        if (!empty($ids)) {
            return array_values(array_unique(array_filter($ids)));
        }

        // 兼容旧 user 表字段 post（逗号分隔岗位 ID）
        $legacyPost = $user->post ?? null;
        if (!$legacyPost) {
            return [];
        }

        $postTable = Schema::hasTable('post') ? 'post' : (Schema::hasTable('posts') ? 'posts' : null);
        if (!$postTable) {
            return [];
        }

        $postIds = array_filter(array_map('intval', explode(',', (string) $legacyPost)));
        if (empty($postIds)) {
            return [];
        }

        $postIdList = implode(',', $postIds);
        $privileges = DB::table($postTable)
            ->whereRaw("id in ({$postIdList})")
            ->where('privilege', '!=', '')
            ->pluck('privilege');

        foreach ($privileges as $privilege) {
            $ids = array_merge($ids, self::parsePrivilegeIds($privilege));
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * @return array<int, int>
     */
    private static function parsePrivilegeIds(mixed $privilege): array
    {
        if (is_array($privilege)) {
            return array_map('intval', $privilege);
        }

        if (!is_string($privilege) || $privilege === '') {
            return [];
        }

        $decoded = json_decode($privilege, true);
        if (is_array($decoded)) {
            return array_map('intval', $decoded);
        }

        return array_values(array_filter(array_map('intval', explode(',', $privilege))));
    }

    /**
     * @param array<int, string> $paths
     * @return array<int, string>
     */
    private static function mergePublicPaths(array $paths): array
    {
        return array_values(array_unique(array_merge($paths, self::publicPaths())));
    }

    /**
     * @return array<int, string>
     */
    private static function publicPaths(): array
    {
        return DB::table('system_menu')
            ->where('status', 0)
            ->pluck('path')
            ->filter()
            ->values()
            ->all();
    }

    private static function legacyUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return str_replace('.', '/', $path) . '.php';
    }
}
