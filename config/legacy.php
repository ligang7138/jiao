<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 旧系统兼容配置
    |--------------------------------------------------------------------------
    */
    'response' => [
        // 兼容阶段同时返回 status/message 字段，便于旧前端平滑切换
        'include_legacy_fields' => true,
    ],

    'permission' => [
        // 是否启用基于旧权限码 module.action 的严格校验
        'use_legacy_code' => true,
    ],

    'auth' => [
        // 生产环境使用旧表 user / post / department
        'user_table' => env('LEGACY_USER_TABLE', 'user'),
        'post_table' => env('LEGACY_POST_TABLE', 'post'),
        'department_table' => env('LEGACY_DEPARTMENT_TABLE', 'department'),
        // 无 is_super 字段时，将这些用户名视为超级管理员（逗号分隔）
        'super_usernames' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('LEGACY_SUPER_USERNAMES', ''))
        ))),
    ],

    'dictionary' => [
        'school_period' => ['幼儿园', '小学', '中学', '职高', '特教', '一贯制'],
    ],
];
