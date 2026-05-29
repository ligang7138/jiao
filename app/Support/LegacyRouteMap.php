<?php

namespace App\Support;

/**
 * 旧系统 do=module.action 与新路由对照。
 * 迁移期持续补充，作为权限映射和回归比对的单一真相源。
 */
class LegacyRouteMap
{
    /**
     * @return array<string, string>
     */
    public static function routeNameToPermission(): array
    {
        return [
            'admin.auth.login' => 'index.login',
            'admin.auth.me' => 'user.index',
            'admin.auth.logout' => 'home.logout',
            'admin.menu.index' => 'user.index',
            'admin.goods.index' => 'goods.index',
            'admin.goods.store' => 'goods.add',
            'admin.goods.update' => 'goods.edit',
            'admin.goods.status' => 'goods.status',
            'admin.goods.import' => 'goods.import',
            'admin.goods.export' => 'goods.export',
            'admin.order.index' => 'order.index',
            'admin.order.show' => 'order.view',
            'admin.order.export' => 'order.export',
            'admin.backorder.index' => 'backorder.index',
            'admin.backorder.audit' => 'backorder.audit',
            'admin.backorder.type.index' => 'backorder.type',
            'admin.supplier.index' => 'supplier.index',
            'admin.supplier.store' => 'supplier.add',
            'admin.supplier.update' => 'supplier.edit',
            'admin.supplier.status' => 'supplier.status',
            'admin.school.index' => 'school.index',
            'admin.school.store' => 'school.add',
            'admin.school.update' => 'school.edit',
            'admin.school.status' => 'school.setStatus',
            'admin.school.show' => 'school.edit',
            'admin.canteen.index' => 'school_canteen.index',
            'admin.canteen.store' => 'school_canteen.add',
            'admin.canteen.update' => 'school_canteen.edit',
            'admin.canteen.status' => 'school_canteen.setStatus',
            'admin.canteen.show' => 'school_canteen.edit',
            'admin.group.index' => 'group.index',
            'admin.group.store' => 'group.add',
            'admin.group.update' => 'group.edit',
            'admin.group.destroy' => 'group.delete',
            'admin.group.show' => 'group.edit',
            'admin.group.canteens' => 'group.school',
            'admin.group.canteen.add' => 'group.school',
            'admin.group.canteen.remove' => 'group.school',
            'admin.group.canteen.set_audit' => 'group.school',
            'admin.group.canteen.remove_audit' => 'group.school',
            'admin.user.index' => 'user.index',
            'admin.user.store' => 'user.add',
            'admin.user.update' => 'user.edit',
            'admin.user.status' => 'user.status',
            'admin.user.privilege' => 'user.privilege',
            'admin.role.index' => 'post.index',
            'admin.role.store' => 'post.add',
            'admin.role.update' => 'post.edit',
            'admin.role.status' => 'post.setStatus',
            'admin.role.privilege' => 'post.privilege',
            'admin.permission.index' => 'privilege.index',
            'admin.permission.store' => 'privilege.add',
            'admin.permission.update' => 'privilege.edit',
            'admin.permission.status' => 'privilege.setStatus',
            'admin.log.index' => 'log.index',
            'admin.log.export' => 'log.export',
            'admin.category.index' => 'category.index',
            'admin.category.store' => 'category.add',
            'admin.category.update' => 'category.edit',
            'admin.category.status' => 'category.status',
            'admin.jiagewang.index' => 'jiagewang.index',
            'admin.jiagewang.import' => 'jiagewang.import',
            'admin.jiagewang.match' => 'jiagewang.match',
            'admin.receivable.index' => 'receivable.order',
            'admin.receivable.receipt' => 'receivable.receipt',
            'admin.receivable.account' => 'receivable.account',
            'admin.bidding.index' => 'bidding.index',
            'admin.approve.comment' => 'approve.comment',
            'admin.approve.complaint' => 'approve.complaint',
            'admin.approve.bidding' => 'approve.bidding',
            'admin.complaint.index' => 'complaint.index',
            'admin.emergency.index' => 'emergency.index',
            'admin.stat.order' => 'stat.order',
        ];
    }
}
