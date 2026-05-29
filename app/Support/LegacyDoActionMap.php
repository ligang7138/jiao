<?php

namespace App\Support;

/**
 * 旧接口 do=module.action 到新 REST 路由的对照关系。
 */
class LegacyDoActionMap
{
    /**
     * @return array<string, array{method:string, uri:string, route_name?:string, permission:string}>
     */
    public static function map(): array
    {
        return [
            'goods.index' => ['method' => 'GET', 'uri' => '/api/v1/admin/goods', 'route_name' => 'admin.goods.index', 'permission' => 'goods.index'],
            'goods.add' => ['method' => 'POST', 'uri' => '/api/v1/admin/goods', 'route_name' => 'admin.goods.store', 'permission' => 'goods.add'],
            'goods.edit' => ['method' => 'PUT', 'uri' => '/api/v1/admin/goods/{id}', 'route_name' => 'admin.goods.update', 'permission' => 'goods.edit'],
            'goods.status' => ['method' => 'PUT', 'uri' => '/api/v1/admin/goods/{id}/status', 'route_name' => 'admin.goods.status', 'permission' => 'goods.status'],
            'goods.import' => ['method' => 'POST', 'uri' => '/api/v1/admin/goods/import', 'route_name' => 'admin.goods.import', 'permission' => 'goods.import'],
            'goods.export' => ['method' => 'GET', 'uri' => '/api/v1/admin/goods/export', 'route_name' => 'admin.goods.export', 'permission' => 'goods.export'],
            'order.index' => ['method' => 'GET', 'uri' => '/api/v1/admin/orders', 'route_name' => 'admin.order.index', 'permission' => 'order.index'],
            'order.view' => ['method' => 'GET', 'uri' => '/api/v1/admin/orders/{id}', 'route_name' => 'admin.order.show', 'permission' => 'order.view'],
            'order.export' => ['method' => 'GET', 'uri' => '/api/v1/admin/orders/export', 'route_name' => 'admin.order.export', 'permission' => 'order.export'],
            'supplier.index' => ['method' => 'GET', 'uri' => '/api/v1/admin/suppliers', 'route_name' => 'admin.supplier.index', 'permission' => 'supplier.index'],
            'supplier.add' => ['method' => 'POST', 'uri' => '/api/v1/admin/suppliers', 'route_name' => 'admin.supplier.store', 'permission' => 'supplier.add'],
            'supplier.edit' => ['method' => 'PUT', 'uri' => '/api/v1/admin/suppliers/{id}', 'route_name' => 'admin.supplier.update', 'permission' => 'supplier.edit'],
            'supplier.status' => ['method' => 'PUT', 'uri' => '/api/v1/admin/suppliers/{id}/status', 'route_name' => 'admin.supplier.status', 'permission' => 'supplier.status'],
            'school.index' => ['method' => 'GET', 'uri' => '/api/v1/admin/schools', 'route_name' => 'admin.school.index', 'permission' => 'school.index'],
            'school.add' => ['method' => 'POST', 'uri' => '/api/v1/admin/schools', 'route_name' => 'admin.school.store', 'permission' => 'school.add'],
            'school.edit' => ['method' => 'PUT', 'uri' => '/api/v1/admin/schools/{id}', 'route_name' => 'admin.school.update', 'permission' => 'school.edit'],
            'user.index' => ['method' => 'GET', 'uri' => '/api/v1/admin/users', 'route_name' => 'admin.user.index', 'permission' => 'user.index'],
            'user.add' => ['method' => 'POST', 'uri' => '/api/v1/admin/users', 'route_name' => 'admin.user.store', 'permission' => 'user.add'],
            'user.edit' => ['method' => 'PUT', 'uri' => '/api/v1/admin/users/{id}', 'route_name' => 'admin.user.update', 'permission' => 'user.edit'],
            'user.status' => ['method' => 'PUT', 'uri' => '/api/v1/admin/users/{id}/status', 'route_name' => 'admin.user.status', 'permission' => 'user.status'],
            'user.privilege' => ['method' => 'PUT', 'uri' => '/api/v1/admin/users/{id}/privilege', 'route_name' => 'admin.user.privilege', 'permission' => 'user.privilege'],
        ];
    }
}
