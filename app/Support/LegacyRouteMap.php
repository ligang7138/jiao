<?php

namespace App\Support;

/**
 * 旧系统 do=module.action 与新路由对照。
 * 迁移期持续补充，作为权限映射和回归比对的单一真相源。
 *
 * 值可以是单个权限码，也可以是权限码数组（满足其一即可）。
 */
class LegacyRouteMap
{
    /**
     * @return array<string, string|array<int, string>>
     */
    public static function routeNameToPermission(): array
    {
        return [
            // 认证
            'admin.auth.login' => 'index.login',
            'admin.auth.me' => 'user.index',
            'admin.auth.logout' => 'home.logout',
            'admin.menu.index' => 'user.index',
            'admin.dictionary.index' => 'user.index',

            // 商品
            'admin.goods.index' => 'goods.index',
            'admin.goods.show' => ['goods.index', 'goods.edit'],
            'admin.goods.store' => 'goods.add',
            'admin.goods.update' => 'goods.edit',
            'admin.goods.destroy' => 'goods.edit',
            'admin.goods.status' => 'goods.status',
            'admin.goods.import' => 'goods.import',
            'admin.goods.export' => 'goods.export',
            'admin.goods.units' => ['goods.index', 'goods.edit', 'goods.add'],
            'admin.goods.batch-delete' => 'goods.edit',
            'admin.goods.batch-publish' => 'goods.upAll',
            'admin.goods.batch-unpublish' => 'goods.downAll',
            'admin.goods.status-log' => ['goods.index', 'goods.edit'],
            'admin.goods.history-price' => ['goods.index', 'goods.edit'],
            'admin.goods.supplier-goods' => ['goods.index', 'goods.edit'],
            'admin.goods.unit.index' => 'goods.unit',
            'admin.goods.unit.add' => 'goods.unit',
            'admin.goods.unit.edit' => 'goods.unit',
            'admin.goods.unit.status' => 'goods.unit',

            // 分类
            'admin.category.index' => 'category.index',
            'admin.category.show' => ['category.index', 'category.edit'],
            'admin.category.store' => 'category.add',
            'admin.category.update' => 'category.edit',
            'admin.category.destroy' => 'category.edit',
            'admin.category.status' => 'category.status',
            'admin.category.tree' => ['category.index', 'category.edit', 'category.add'],
            'admin.category.top' => ['category.index', 'category.edit', 'goods.index', 'goods.edit', 'goods.add'],
            'admin.category.children' => ['category.index', 'category.edit', 'goods.index', 'goods.edit'],

            // 订单
            'admin.order.index' => 'order.index',
            'admin.order.show' => 'order.view',
            'admin.order.export' => 'order.export',
            'admin.order.trace-source' => ['order.index', 'order.view'],

            // 退货
            'admin.backorder.index' => 'backorder.index',
            'admin.backorder.show' => 'backorder.index',
            'admin.backorder.audit' => 'backorder.audit',
            'admin.backorder.type.index' => 'backorder.type',

            // 供应商
            'admin.supplier.index' => 'supplier.index',
            'admin.supplier.show' => ['supplier.index', 'supplier.edit'],
            'admin.supplier.store' => 'supplier.add',
            'admin.supplier.update' => 'supplier.edit',
            'admin.supplier.status' => 'supplier.status',
            'admin.supplier.active' => ['supplier.index', 'goods.index', 'order.index'],

            // 学校 / 食堂
            'admin.school.index' => 'school.index',
            'admin.school.options' => ['school.index', 'school.add', 'school.edit'],
            'admin.school.store' => 'school.add',
            'admin.school.update' => 'school.edit',
            'admin.school.status' => 'school.setStatus',
            'admin.school.show' => ['school.index', 'school.edit'],
            'admin.school.active' => ['school.index', 'order.index', 'group.index'],
            'admin.canteen.index' => 'school_canteen.index',
            'admin.canteen.store' => 'school_canteen.add',
            'admin.canteen.update' => 'school_canteen.edit',
            'admin.canteen.status' => 'school_canteen.setStatus',
            'admin.canteen.show' => ['school_canteen.index', 'school_canteen.edit'],
            'admin.canteen.active' => ['school_canteen.index', 'order.index', 'group.index'],

            // 分组
            'admin.group.index' => 'group.index',
            'admin.group.store' => 'group.add',
            'admin.group.update' => 'group.edit',
            'admin.group.destroy' => 'group.delete',
            'admin.group.show' => ['group.index', 'group.edit'],
            'admin.group.options' => ['group.index', 'group.edit', 'group.add'],
            'admin.group.canteens' => 'group.school',
            'admin.group.canteen.add' => 'group.school',
            'admin.group.canteen.remove' => 'group.school',
            'admin.group.canteen.set_audit' => 'group.school',
            'admin.group.canteen.remove_audit' => 'group.school',

            // 用户 / 角色 / 权限
            'admin.user.index' => 'user.index',
            'admin.user.show' => ['user.index', 'user.edit'],
            'admin.user.store' => 'user.add',
            'admin.user.update' => 'user.edit',
            'admin.user.status' => 'user.status',
            'admin.user.privilege' => 'user.privilege',
            'admin.user.options' => ['user.index', 'user.edit', 'user.add'],
            'admin.role.index' => 'post.index',
            'admin.role.show' => ['post.index', 'post.edit'],
            'admin.role.store' => 'post.add',
            'admin.role.update' => 'post.edit',
            'admin.role.status' => 'post.setStatus',
            'admin.role.privilege' => 'post.privilege',
            'admin.role.options' => ['post.index', 'post.edit', 'user.index'],
            'admin.permission.index' => 'privilege.index',
            'admin.permission.show' => ['privilege.index', 'privilege.edit'],
            'admin.permission.store' => 'privilege.add',
            'admin.permission.update' => 'privilege.edit',
            'admin.permission.status' => 'privilege.setStatus',
            'admin.permission.tree' => ['privilege.index', 'user.privilege', 'post.privilege'],

            // 日志
            'admin.log.index' => 'log.index',
            'admin.log.show' => 'log.index',
            'admin.log.export' => 'log.export',

            // 应收 / 招投标 / 审批
            'admin.receivable.index' => 'receivable.order',
            'admin.receivable.receipt' => 'receivable.receipt',
            'admin.receivable.account' => 'receivable.account',
            'admin.bidding.index' => 'bidding.index',
            'admin.bidding.show' => 'bidding.index',
            'admin.approve.comment' => 'approve.comment',
            'admin.approve.complaint' => 'approve.complaint',
            'admin.approve.bidding' => 'approve.bidding',

            // 投诉 / 应急
            'admin.complaint.index' => 'complaint.index',
            'admin.complaint.show' => 'complaint.index',
            'admin.emergency.index' => 'emergency.index',
            'admin.emergency.show' => 'emergency.index',

            // 统计
            'admin.stat.order' => 'stat.order',

            // 价格网
            'admin.jiagewang.index' => 'jiagewang.index',
            'admin.jiagewang.import' => 'jiagewang.import',
            'admin.jiagewang.match' => 'jiagewang.match',
        ];
    }
}
