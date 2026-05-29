<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * 请求日志中间件
 */
class RequestLog
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // 记录请求信息
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'params' => $this->filterSensitiveData($request->all()),
        ];

        Log::channel('api')->info('API Request', $logData);

        $response = $next($request);

        // 记录响应信息
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('api')->info('API Response', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'user_id' => auth()->id(),
        ]);

        return $response;
    }

    /**
     * 过滤敏感数据
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'old_password', 'new_password', 'token', 'secret'];

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '******';
            }
        }

        return $data;
    }
}
