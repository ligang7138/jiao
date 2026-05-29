<?php

namespace App\Support;

/**
 * 旧 system_menu.path 到新 Vue 路由映射。
 */
class LegacyMenuPathMap
{
    /**
     * @return array<string, string>
     */
    public static function pathToRoute(): array
    {
        return [
            'home.index' => '/dashboard',
            'goods.index' => '/goods/list',
            'goods.unit' => '/goods/unit',
            'goods.report' => '/goods/list',
            'category.index' => '/goods/category',
            'order.index' => '/orders/list',
            'order.type' => '/orders/list',
            'supplier.index' => '/suppliers/list',
            'school.index' => '/schools/list',
            'school_canteen.index' => '/schools/canteens',
            'school_district.index' => '/schools/list',
            'user.index' => '/system/users',
            'post.index' => '/system/roles',
            'department.index' => '/system/users',
            'privilege.index' => '/system/permissions',
            'bidding.index' => '/bidding/histories',
            'bidding.discount' => '/bidding/discounts',
            'backorder.index' => '/backorder/list',
            'backorder.type' => '/backorder/type',
            'receivable.receipt' => '/receivable/receipt',
            'receivable.account' => '/receivable/account',
            'receivable.order' => '/receivable/receipt',
            'receivable.accountNo' => '/receivable/account/no-receipt',
            'receivable.import' => '/receivable/receipt',
            'approve.comment' => '/approve/comment',
            'approve.complaint' => '/approve/complaint',
            'approve.bidding' => '/approve/bidding',
            'complaint.index' => '/complaint/list',
            'emergency.index' => '/emergency/list',
            'emergency.type' => '/emergency/list',
            'group.index' => '/group/list',
            'comment.index' => '/approve/comment',
            'stat.order' => '/stat/order',
            'stat.goods' => '/stat/order',
            'stat.bidding' => '/stat/order',
            'stat.complaint' => '/stat/order',
            'stat.backorder' => '/stat/order',
            'stat.replenish' => '/stat/order',
            'stat.ontime_rate' => '/stat/order',
            'stat.backorder_rate' => '/stat/order',
            'stat.replenish_rate' => '/stat/order',
            'log.index' => '/system/logs',
            'home.orders' => '/dashboard',
            'home.supplier' => '/dashboard',
            'data_analysis.supplier' => '/dashboard',
            'data_analysis.goods' => '/dashboard',
            'data_analysis.school_canteen' => '/dashboard',
            'approve.supplier' => '/approve/bidding',
            'approve.audit' => '/approve/bidding',
        ];
    }

    public static function resolve(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $map = self::pathToRoute();
        if (isset($map[$path])) {
            return $map[$path];
        }

        // 兜底：module.index -> /{module}/list（处理 orders 等复数差异）
        if (preg_match('/^([a-z0-9_]+)\.index$/', $path, $matches)) {
            $module = $matches[1];
            $folder = match ($module) {
                'order' => 'orders',
                'supplier' => 'suppliers',
                'school' => 'schools',
                default => $module,
            };

            return '/' . $folder . '/list';
        }

        // module.action -> /module/action
        if (str_contains($path, '.')) {
            [$module, $action] = explode('.', $path, 2);

            return '/' . $module . '/' . str_replace('_', '-', $action);
        }

        return null;
    }
}
