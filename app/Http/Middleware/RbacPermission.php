<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;
use App\Support\LegacyPermission;

/**
 * RBAC 权限检查中间件
 */
class RbacPermission
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $routeName = $request->route()?->getName();

        // 未登录
        if (!$user) {
            return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '请先登录');
        }

        // 超级管理员跳过权限检查
        if ($user->isSuper()) {
            return $next($request);
        }

        // 优先使用旧权限映射（module.action）
        $permissionCode = LegacyPermission::resolveByRouteName($routeName)
            ?: $this->getPermissionCode($request);

        // 如果没有权限码配置，默认放行
        if (!$permissionCode) {
            return $next($request);
        }

        // 检查权限
        if (!$this->hasPermission($user, $permissionCode)) {
            return ResponseHelper::error(ErrorCode::LEGACY_PERMISSION_DENIED, '对不起，您没有操作权限！');
        }

        return $next($request);
    }

    /**
     * 获取当前路由的权限码
     */
    private function getPermissionCode(Request $request): ?string
    {
        $route = $request->route();

        if (!$route) {
            return null;
        }

        $action = $route->getActionName();

        // 解析 Controller@method
        if (preg_match('/\\\\(\w+)Controller@(\w+)$/', $action, $matches)) {
            $module = $this->camelToSnake($matches[1]);
            $method = $matches[2];

            return $this->mapMethodToPermission($module, $method);
        }

        return null;
    }

    /**
     * 驼峰转蛇形
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * 将控制器方法映射为权限码
     */
    private function mapMethodToPermission(string $module, string $method): string
    {
        // 方法名到权限动作的映射
        $map = [
            'index' => 'index',
            'store' => 'add',
            'show' => 'view',
            'update' => 'edit',
            'destroy' => 'delete',
            'export' => 'export',
            'import' => 'import',
            'publish' => 'publish',
            'unpublish' => 'unpublish',
            'batchPublish' => 'batch_publish',
            'batchUnpublish' => 'batch_unpublish',
            'updateStatus' => 'status',
            'resetPassword' => 'reset_password',
            'updatePermissions' => 'permission',
            'tree' => 'tree',
            'fix' => 'fix',
            'batchFix' => 'batch_fix',
            'audit' => 'audit',
            'reject' => 'reject',
            'cancel' => 'cancel',
            'updateSolution' => 'solution',
            'process' => 'process',
            'review' => 'review',
        ];

        $action = $map[$method] ?? $method;

        return "{$module}.{$action}";
    }

    /**
     * 检查用户是否拥有指定权限
     */
    private function hasPermission($user, string $permissionCode): bool
    {
        // 从用户权限列表中检查
        $permissions = $user->privilege ?? $user->permissions ?? [];
        if (!is_array($permissions)) {
            return false;
        }
        if (in_array('*', $permissions, true)) {
            return true;
        }

        // 支持通配符匹配
        foreach ($permissions as $permission) {
            if ($permission === $permissionCode) {
                return true;
            }

            // 通配符匹配 (如 goods.* 匹配 goods.index, goods.add 等)
            if (str_ends_with($permission, '.*')) {
                $prefix = substr($permission, 0, -2);
                if (str_starts_with($permissionCode, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }
}
