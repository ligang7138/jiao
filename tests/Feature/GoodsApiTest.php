<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin\User;
use App\Models\Goods\Goods;
use App\Models\Goods\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 商品 API 功能测试
 */
class GoodsApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建测试用户
        $this->user = User::factory()->create([
            'username' => 'testuser',
            'name' => 'Test User',
        ]);

        // 生成 JWT Token
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * 测试获取商品列表 - 已登录
     */
    public function test_get_goods_list_with_auth(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/admin/goods');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'message',
                'data' => [
                    'list',
                    'total',
                    'page',
                    'page_size',
                ],
            ]);
    }

    /**
     * 测试获取商品列表 - 未登录
     */
    public function test_get_goods_list_without_auth(): void
    {
        $response = $this->getJson('/api/v1/admin/goods');

        $response->assertStatus(401);
    }

    /**
     * 测试获取商品单位列表 - 未登录
     */
    public function test_get_units_without_auth(): void
    {
        $response = $this->getJson('/api/v1/admin/goods/units');

        $response->assertStatus(401);
    }

    /**
     * 测试获取分类树 - 已登录
     */
    public function test_get_category_tree(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/admin/categories/tree');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'data',
            ]);
    }
}