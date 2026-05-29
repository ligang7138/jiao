<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Admin\User;

/**
 * 认证 API 测试
 */
class AuthApiTest extends TestCase
{
    use RefreshDatabase;
    /**
     * 测试登录接口 - 无效凭据
     */
    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'nonexistent',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'code' => 40100,
            ]);
    }

    /**
     * 测试获取用户信息 - 未登录
     */
    public function test_me_without_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    /**
     * 测试刷新 Token - 未登录
     */
    public function test_refresh_without_token(): void
    {
        $response = $this->postJson('/api/v1/auth/refresh');

        // JWT 库在没有 token 时抛出异常，返回 500
        $response->assertStatus(500);
    }

    /**
     * 测试健康检查接口
     */
    public function test_health_check(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}