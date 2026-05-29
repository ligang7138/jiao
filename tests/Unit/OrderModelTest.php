<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;

/**
 * 订单模型单元测试
 */
class OrderModelTest extends TestCase
{
    /**
     * 测试订单状态常量定义
     */
    public function test_status_constants(): void
    {
        $this->assertEquals(0, Order::STATUS_DRAFT);
        $this->assertEquals(1, Order::STATUS_PENDING);
        $this->assertEquals(2, Order::STATUS_APPROVED);
        $this->assertEquals(3, Order::STATUS_SHIPPED);
        $this->assertEquals(4, Order::STATUS_RECEIVED);
        $this->assertEquals(5, Order::STATUS_CANCELLED);
    }

    /**
     * 测试订单状态文本映射
     */
    public function test_status_text_mapping(): void
    {
        $order = new Order();

        $order->status = Order::STATUS_DRAFT;
        $this->assertEquals('草稿', $order->getStatusText());

        $order->status = Order::STATUS_PENDING;
        $this->assertEquals('待审核', $order->getStatusText());

        $order->status = Order::STATUS_APPROVED;
        $this->assertEquals('已审核', $order->getStatusText());

        $order->status = Order::STATUS_SHIPPED;
        $this->assertEquals('已发货', $order->getStatusText());

        $order->status = Order::STATUS_RECEIVED;
        $this->assertEquals('已收货', $order->getStatusText());

        $order->status = Order::STATUS_CANCELLED;
        $this->assertEquals('已取消', $order->getStatusText());

        $order->status = 999;
        $this->assertEquals('未知', $order->getStatusText());
    }

    /**
     * 测试订单模型属性填充
     */
    public function test_fillable_attributes(): void
    {
        $order = new Order();

        $fillable = $order->getFillable();

        $this->assertContains('order_no', $fillable);
        $this->assertContains('school_id', $fillable);
        $this->assertContains('canteen_id', $fillable);
        $this->assertContains('supplier_id', $fillable);
        $this->assertContains('total_amount', $fillable);
        $this->assertContains('status', $fillable);
    }

    /**
     * 测试订单模型 casts 属性
     */
    public function test_casts_attributes(): void
    {
        $order = new Order();

        $casts = $order->getCasts();

        $this->assertEquals('decimal:2', $casts['total_amount']);
        $this->assertEquals('date', $casts['order_date']);
        $this->assertEquals('date', $casts['delivery_date']);
        $this->assertEquals('integer', $casts['status']);
        $this->assertEquals('datetime', $casts['created_at']);
    }

    /**
     * 测试订单表名设置
     */
    public function test_table_name(): void
    {
        $order = new Order();
        $this->assertEquals('orders', $order->getTable());
    }
}