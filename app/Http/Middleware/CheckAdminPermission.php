<?php

namespace App\Http\Middleware;

use App\Constants\ErrorCode;
use App\Helpers\ResponseHelper;
use App\Support\LegacyPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 严格按旧权限码（module.action）做权限校验。
 */
class CheckAdminPermission
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

        $legacyPermission = LegacyPermission::resolveByRouteName($request->route()?->getName());
        if (!$legacyPermission) {
            return $next($request);
        }

        $permissions = $user->privilege ?? $user->permissions ?? [];
        if (!is_array($permissions) || !in_array($legacyPermission, $permissions, true)) {
            return ResponseHelper::error(ErrorCode::LEGACY_PERMISSION_DENIED, '对不起，您没有操作权限！');
        }

        return $next($request);
    }
}
