<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 跨域处理中间件
 */
class CrossDomain
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $origin = $request->header('Origin');

        // 允许的域名列表
        $allowedOrigins = config('cors.allowed_origins', ['*']);

        // 检查是否允许该域名
        $allowOrigin = '*';
        if (!in_array('*', $allowedOrigins)) {
            if (in_array($origin, $allowedOrigins)) {
                $allowOrigin = $origin;
            }
        }

        $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept, Origin, X-Requested-With, X-Token');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
