<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Goods\Goods;
use App\Models\Goods\Category;

/**
 * 商品模型单元测试
 */
class GoodsModelTest extends TestCase
{
    /**
     * 测试状态常量定义
     */
    public function test_status_constants(): void
    {
        $this->assertEquals(0, Goods::STATUS_OFF);
        $this->assertEquals(1, Goods::STATUS_ON);
        $this->assertEquals(2, Goods::STATUS_AUDIT);
    }

    /**
     * 测试状态文本映射
     */
    public function test_status_text_mapping(): void
    {
        $goods = new Goods();

        $goods->status = Goods::STATUS_OFF;
        $this->assertEquals('已下架', $goods->getStatusText());

        $goods->status = Goods::STATUS_ON;
        $this->assertEquals('已上架', $goods->getStatusText());

        $goods->status = Goods::STATUS_AUDIT;
        $this->assertEquals('待审核', $goods->getStatusText());

        $goods->status = 999;
        $this->assertEquals('未知', $goods->getStatusText());
    }

    /**
     * 测试模型属性填充
     */
    public function test_fillable_attributes(): void
    {
        $goods = new Goods();

        $fillable = $goods->getFillable();

        $this->assertContains('goods_name', $fillable);
        $this->assertContains('goods_sn', $fillable);
        $this->assertContains('category_id', $fillable);
        $this->assertContains('unit', $fillable);
        $this->assertContains('price', $fillable);
        $this->assertContains('status', $fillable);
    }

    /**
     * 测试模型 casts 属性
     */
    public function test_casts_attributes(): void
    {
        $goods = new Goods();

        $casts = $goods->getCasts();

        $this->assertEquals('decimal:2', $casts['price']);
        $this->assertEquals('integer', $casts['status']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    /**
     * 测试表名设置
     */
    public function test_table_name(): void
    {
        $goods = new Goods();
        $this->assertEquals('goods', $goods->getTable());
    }
}