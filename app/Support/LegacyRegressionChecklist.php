<?php

namespace App\Support;

class LegacyRegressionChecklist
{
    /**
     * @return array<int, string>
     */
    public static function defaultChecks(): array
    {
        return [
            '菜单可见性与旧系统一致',
            '按钮权限与旧 system_menu.path 一致',
            '查询字段名、默认值、分页参数与旧页面一致',
            '列表总数、排序、字段展示、统计值与旧页面一致',
            '表单校验触发时机与提示文案与旧页面一致',
            '弹窗关闭后的父列表刷新行为与旧 layer iframe 一致',
            '业务操作后的数据库变更、状态流转、日志写入与旧系统一致',
            '导出文件字段、顺序、数据口径与旧导出一致',
            '无权限返回 code=40098 且文案为“对不起，您没有操作权限！”',
        ];
    }
}
