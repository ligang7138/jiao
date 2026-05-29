<?php

namespace Tests\Unit;

use App\Support\LegacyRegressionChecklist;
use App\Support\ModuleMigrationRegistry;
use Tests\TestCase;

class ModuleMigrationRegistryTest extends TestCase
{
    public function test_core_modules_are_registered(): void
    {
        $modules = ModuleMigrationRegistry::modules();

        $this->assertArrayHasKey('goods', $modules);
        $this->assertArrayHasKey('order', $modules);
        $this->assertArrayHasKey('receivable', $modules);
        $this->assertSame('high', $modules['order']['risk']);
    }

    public function test_default_regression_checks_cover_permission_and_export(): void
    {
        $checks = LegacyRegressionChecklist::defaultChecks();

        $this->assertContains('按钮权限与旧 system_menu.path 一致', $checks);
        $this->assertContains('导出文件字段、顺序、数据口径与旧导出一致', $checks);
    }
}
