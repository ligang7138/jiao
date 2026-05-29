<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Excel 导出服务测试
 */
class ExcelExportServiceTest extends TestCase
{
    /**
     * 测试导出基本功能
     */
    public function test_export_creates_file(): void
    {
        $service = new \App\Services\Common\ExcelExportService();

        $headers = ['ID', '名称', '价格'];
        $data = [
            [1, '商品A', 100],
            [2, '商品B', 200],
            [3, '商品C', 300],
        ];

        $filePath = $service->export($headers, $data, 'test_export');

        // 验证文件存在
        $this->assertFileExists($filePath);

        // 验证文件是 Excel 格式
        $this->assertStringEndsWith('.xlsx', $filePath);

        // 清理测试文件
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 测试空数据导出
     */
    public function test_export_empty_data(): void
    {
        $service = new \App\Services\Common\ExcelExportService();

        $headers = ['ID', '名称'];
        $data = [];

        $filePath = $service->export($headers, $data, 'empty_export');

        $this->assertFileExists($filePath);

        // 清理
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * 测试获取列字母
     */
    public function test_get_column_letter(): void
    {
        $service = new \App\Services\Common\ExcelExportService();

        // 使用反射测试私有方法
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getColumnLetter');
        $method->setAccessible(true);

        $this->assertEquals('A', $method->invoke($service, 1));
        $this->assertEquals('B', $method->invoke($service, 2));
        $this->assertEquals('Z', $method->invoke($service, 26));
        $this->assertEquals('AA', $method->invoke($service, 27));
        $this->assertEquals('AB', $method->invoke($service, 28));
    }
}