<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\RateLimiter;
use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;

/**
 * API 限流中间件
 */
class RateLimit
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return ResponseHelper::error(
                ErrorCode::BUSINESS_LIMIT,
                '请求过于频繁，请稍后再试'
            )->withHeaders([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => RateLimiter::availableIn($key),
            ]);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => RateLimiter::availableIn($key),
        ]);
    }

    /**
     * 解析请求签名
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = auth()->id() ?: $request->ip();

        return sha1($userId . '|' . $request->route()?->getName() ?: $request->path());
    }
}
