<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin\User;
use App\Models\Admin\Department;
use App\Models\Admin\Post;
use App\Models\Admin\Permission;
use App\Models\Admin\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * 运行数据库填充
     */
    public function run(): void
    {
        // 创建默认部门
        $department = Department::create([
            'name' => '技术部',
            'sort' => 1,
            'status' => 1,
        ]);

        // 创建超级管理员岗位
        $superPost = Post::create([
            'name' => '超级管理员',
            'code' => 'super_admin',
            'department_id' => $department->id,
            'remark' => '系统超级管理员',
            'status' => 1,
        ]);

        // 创建普通管理员岗位
        $adminPost = Post::create([
            'name' => '管理员',
            'code' => 'admin',
            'department_id' => $department->id,
            'remark' => '普通管理员',
            'status' => 1,
        ]);

        // 创建超级管理员用户
        $superUser = User::create([
            'username' => 'admin',
            'password' => Hash::make('Dxdzcg888'),
            'salt' => '',
            'name' => '超级管理员',
            'department_id' => $department->id,
            'is_super' => true,
            'status' => 1,
        ]);

        // 关联岗位
        $superUser->posts()->attach($superPost->id);

        // 创建测试管理员用户
        $testUser = User::create([
            'username' => 'test',
            'password' => Hash::make('Dxdzcg888'),
            'salt' => '',
            'name' => '测试管理员',
            'department_id' => $department->id,
            'is_super' => false,
            'status' => 1,
        ]);

        $testUser->posts()->attach($adminPost->id);

        // 创建权限菜单
        $this->createPermissions();

        $this->command->info('数据库初始化完成！');
        $this->command->info('默认账号: admin / Dxdzcg888');
    }

    /**
     * 创建权限菜单
     */
    private function createPermissions(): void
    {
        $menus = [
            [
                'name' => '系统管理',
                'code' => 'system',
                'type' => 1,
                'icon' => 'setting',
                'sort' => 100,
                'children' => [
                    [
                        'name' => '用户管理',
                        'code' => 'users',
                        'type' => 1,
                        'path' => '/system/users',
                        'component' => 'system/users/index',
                        'sort' => 1,
                    ],
                    [
                        'name' => '岗位管理',
                        'code' => 'posts',
                        'type' => 1,
                        'path' => '/system/posts',
                        'component' => 'system/posts/index',
                        'sort' => 2,
                    ],
                    [
                        'name' => '部门管理',
                        'code' => 'departments',
                        'type' => 1,
                        'path' => '/system/departments',
                        'component' => 'system/departments/index',
                        'sort' => 3,
                    ],
                ],
            ],
            [
                'name' => '商品管理',
                'code' => 'goods',
                'type' => 1,
                'icon' => 'goods',
                'sort' => 10,
                'children' => [
                    [
                        'name' => '商品列表',
                        'code' => 'goods_list',
                        'type' => 1,
                        'path' => '/goods/list',
                        'component' => 'goods/list/index',
                        'sort' => 1,
                    ],
                    [
                        'name' => '分类管理',
                        'code' => 'category',
                        'type' => 1,
                        'path' => '/goods/category',
                        'component' => 'goods/category/index',
                        'sort' => 2,
                    ],
                ],
            ],
            [
                'name' => '订单管理',
                'code' => 'orders',
                'type' => 1,
                'icon' => 'order',
                'sort' => 20,
                'children' => [
                    [
                        'name' => '订单列表',
                        'code' => 'orders_list',
                        'type' => 1,
                        'path' => '/orders/list',
                        'component' => 'orders/list/index',
                        'sort' => 1,
                    ],
                    [
                        'name' => '退货单',
                        'code' => 'backorder',
                        'type' => 1,
                        'path' => '/orders/backorder',
                        'component' => 'orders/backorder/index',
                        'sort' => 2,
                    ],
                ],
            ],
            [
                'name' => '供应商管理',
                'code' => 'suppliers',
                'type' => 1,
                'icon' => 'supplier',
                'sort' => 30,
                'children' => [
                    [
                        'name' => '供应商列表',
                        'code' => 'suppliers_list',
                        'type' => 1,
                        'path' => '/suppliers/list',
                        'component' => 'suppliers/list/index',
                        'sort' => 1,
                    ],
                ],
            ],
            [
                'name' => '学校管理',
                'code' => 'schools',
                'type' => 1,
                'icon' => 'school',
                'sort' => 40,
                'children' => [
                    [
                        'name' => '学校列表',
                        'code' => 'schools_list',
                        'type' => 1,
                        'path' => '/schools/list',
                        'component' => 'schools/list/index',
                        'sort' => 1,
                    ],
                    [
                        'name' => '食堂管理',
                        'code' => 'canteens',
                        'type' => 1,
                        'path' => '/schools/canteens',
                        'component' => 'schools/canteens/index',
                        'sort' => 2,
                    ],
                ],
            ],
        ];

        foreach ($menus as $menu) {
            $this->createMenu($menu, 0);
        }
    }

    /**
     * 创建菜单
     */
    private function createMenu(array $menu, int $parentId): void
    {
        $children = $menu['children'] ?? [];
        unset($menu['children']);

        $menu['parent_id'] = $parentId;
        $menu['status'] = $menu['status'] ?? 1;

        $permission = Permission::create($menu);

        foreach ($children as $child) {
            $this->createMenu($child, $permission->id);
        }
    }
}
