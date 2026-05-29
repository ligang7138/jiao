<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin\User;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Models\Supplier\Supplier;
use App\Models\School\School;
use App\Models\School\Canteen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 订单 API 功能测试
 */
class OrderApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试用户
        $this->user = User::factory()->create();

        // 生成 JWT Token
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * 测试获取订单列表 - 已登录
     */
    public function test_get_order_list_with_auth(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/admin/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'list',
                    'total',
                ],
            ]);
    }

    /**
     * 测试获取订单列表 - 未登录
     */
    public function test_get_order_list_without_auth(): void
    {
        $response = $this->getJson('/api/v1/admin/orders');

        $response->assertStatus(401);
    }
}