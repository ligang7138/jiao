<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuth
{
    /**
     * JWT 认证中间件
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 尝试解析并验证 token
            $token = JWTAuth::parseToken();

            if (!$token->authenticate()) {
                return ResponseHelper::error(ErrorCode::TOKEN_INVALID, 'Token无效');
            }
        } catch (TokenExpiredException $e) {
            return ResponseHelper::error(ErrorCode::TOKEN_EXPIRED, 'Token已过期');
        } catch (TokenInvalidException $e) {
            return ResponseHelper::error(ErrorCode::TOKEN_INVALID, 'Token无效');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '未授权访问');
        }

        return $next($request);
    }
}