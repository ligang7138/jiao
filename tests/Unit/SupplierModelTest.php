<?php

namespace Tests\Unit;

use App\Models\Supplier\Supplier;
use PHPUnit\Framework\TestCase;

/**
 * 供应商模型单元测试
 */
class SupplierModelTest extends TestCase
{
    public function test_status_constants(): void
    {
        $this->assertEquals(0, Supplier::STATUS_DISABLED);
        $this->assertEquals(1, Supplier::STATUS_ENABLED);
    }

    public function test_fillable_attributes(): void
    {
        $supplier = new Supplier();
        $fillable = $supplier->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('linkman', $fillable);
        $this->assertContains('mobile', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_casts_attributes(): void
    {
        $supplier = new Supplier();
        $casts = $supplier->getCasts();

        $this->assertEquals('integer', $casts['status']);
        $this->assertEquals('integer', $casts['add_time']);
    }

    public function test_table_name(): void
    {
        $supplier = new Supplier();
        $this->assertEquals('supplier', $supplier->getTable());
    }

    public function test_has_goods_relation_methods(): void
    {
        $this->assertTrue(method_exists(Supplier::class, 'goodsPrices'));
        $this->assertTrue(method_exists(Supplier::class, 'goods'));
    }
}
