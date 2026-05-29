<?php

namespace App\Http\Middleware;

use App\Constants\ErrorCode;
use App\Helpers\ResponseHelper;
use App\Support\LegacyPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC 权限检查中间件（严格对齐旧系统 module.action 权限码）。
 */
class RbacPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '请先登录');
        }

        if ($user->isSuper()) {
            return $next($request);
        }

        $permissionCode = LegacyPermission::resolveByRouteName($request->route()?->getName());

        if ($permissionCode === null) {
            return $next($request);
        }

        if (!LegacyPermission::userHasPermission($user, $permissionCode)) {
            return ResponseHelper::error(ErrorCode::LEGACY_PERMISSION_DENIED, '对不起，您没有操作权限！');
        }

        return $next($request);
    }
}
