<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Excel 导入服务测试
 */
class ExcelImportServiceTest extends TestCase
{
    /**
     * 测试验证必填字段
     */
    public function test_validate_required_fields(): void
    {
        $service = new \App\Services\Common\ExcelImportService();

        $data = [
            ['商品名称' => '测试商品', '单位' => '斤'],
            ['商品名称' => '', '单位' => '公斤'],
            ['商品名称' => '商品C', '单位' => null],
        ];

        $rules = [
            '商品名称' => ['required'],
            '单位' => ['required'],
        ];

        $result = $service->validate($data, $rules);

        // 第一条数据有效
        $this->assertCount(1, $result['valid']);
        $this->assertEquals('测试商品', $result['valid'][0]['商品名称']);

        // 后两条数据无效
        $this->assertCount(2, $result['errors']);
    }

    /**
     * 测试验证最大长度
     */
    public function test_validate_max_length(): void
    {
        $service = new \App\Services\Common\ExcelImportService();

        // 创建超过50个字符的长字符串（60个字符）
        $longName = str_repeat('测试商品', 15); // 60个字符 (4*15=60)

        $data = [
            ['商品名称' => '短名称'],
            ['商品名称' => $longName],
        ];

        $rules = [
            '商品名称' => ['required', 'max:50'],
        ];

        $result = $service->validate($data, $rules);

        $this->assertCount(1, $result['valid']);
        $this->assertCount(1, $result['errors']);
    }

    /**
     * 测试验证数字类型
     */
    public function test_validate_numeric(): void
    {
        $service = new \App\Services\Common\ExcelImportService();

        $data = [
            ['价格' => '100'],
            ['价格' => 'abc'],
            ['价格' => '50.5'],
        ];

        $rules = [
            '价格' => ['numeric'],
        ];

        $result = $service->validate($data, $rules);

        $this->assertCount(2, $result['valid']);
        $this->assertCount(1, $result['errors']);
    }

    /**
     * 测试空数据验证
     */
    public function test_validate_empty_data(): void
    {
        $service = new \App\Services\Common\ExcelImportService();

        $data = [];
        $rules = ['商品名称' => ['required']];

        $result = $service->validate($data, $rules);

        $this->assertCount(0, $result['valid']);
        $this->assertCount(0, $result['errors']);
    }
}