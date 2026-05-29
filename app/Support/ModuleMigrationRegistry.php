<?php

namespace App\Support;

class ModuleMigrationRegistry
{
    /**
     * @return array<string, array{risk:string, legacy_module:string, status:string, checks:array<int,string>}>
     */
    public static function modules(): array
    {
        return [
            'category' => self::entry('low', 'category'),
            'department' => self::entry('low', 'department'),
            'post' => self::entry('low', 'post'),
            'privilege' => self::entry('low', 'privilege'),
            'user' => self::entry('low', 'user'),
            'goods' => self::entry('medium', 'goods'),
            'supplier' => self::entry('medium', 'supplier'),
            'school' => self::entry('medium', 'school'),
            'school_canteen' => self::entry('medium', 'school_canteen'),
            'order' => self::entry('high', 'order'),
            'backorder' => self::entry('high', 'backorder'),
            'receivable' => self::entry('high', 'receivable'),
            'bidding' => self::entry('high', 'bidding'),
            'approve' => self::entry('high', 'approve'),
            'jiagewang' => self::entry('high', 'jiagewang'),
            'stat' => self::entry('medium', 'stat'),
            'complaint' => self::entry('medium', 'complaint'),
            'emergency' => self::entry('medium', 'emergency'),
            'group' => self::entry('medium', 'group'),
        ];
    }

    /**
     * @return array{risk:string, legacy_module:string, status:string, checks:array<int,string>}
     */
    private static function entry(string $risk, string $legacyModule): array
    {
        return [
            'risk' => $risk,
            'legacy_module' => $legacyModule,
            'status' => 'scaffolded',
            'checks' => [
                'same_permission_visibility',
                'same_query_params',
                'same_list_total_and_order',
                'same_form_validation_message',
                'same_business_status_transition',
                'same_export_columns_and_rows',
            ],
        ];
    }
}
