<?php

namespace App\Services\Admin;

use App\Models\Admin\User;
use App\Models\Admin\Post;
use App\Models\Admin\Department;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * 用户服务层
 */
class UserService
{
    /**
     * 获取用户列表
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = User::with(['department', 'posts']);

        // 搜索条件
        if (!empty($params['username'])) {
            $query->where('username', 'like', "%{$params['username']}%");
        }

        if (!empty($params['name'])) {
            $query->where('name', 'like', "%{$params['name']}%");
        }

        if (!empty($params['department_id'])) {
            $query->where('department_id', $params['department_id']);
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', (int) $params['status']);
        }

        if (!empty($params['post_id'])) {
            $query->whereHas('posts', function ($q) use ($params) {
                $q->where('posts.id', $params['post_id']);
            });
        }

        // 排序
        $query->orderBy('id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'username' => $item->username,
                    'name' => $item->name,
                    'email' => $item->email,
                    'mobile' => $item->mobile,
                    'avatar' => $item->avatar,
                    'department_id' => $item->department_id,
                    'department_name' => $item->department?->name,
                    'posts' => $item->posts->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                    ]),
                    'is_super' => $item->is_super,
                    'status' => $item->status,
                    'last_login_time' => $item->last_login_time ? date('Y-m-d H:i:s', $item->last_login_time) : null,
                    'last_login_ip' => $item->last_login_ip,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取用户详情
     */
    public function getDetail(int $id): ?array
    {
        $user = User::with(['department', 'posts'])->find($id);

        if (!$user) {
            return null;
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'avatar' => $user->avatar,
            'department_id' => $user->department_id,
            'department_name' => $user->department?->name,
            'post_ids' => $user->posts->pluck('id')->toArray(),
            'is_super' => $user->is_super,
            'status' => $user->status,
            'last_login_time' => $user->last_login_time ? date('Y-m-d H:i:s', $user->last_login_time) : null,
            'last_login_ip' => $user->last_login_ip,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 创建用户
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // 加密密码
            $data['password'] = AuthHelper::encryptPassword($data['password'] ?? AuthHelper::DEFAULT_PASSWORD);
            $data['salt'] = '';

            // 提取岗位ID
            $postIds = $data['post_ids'] ?? [];
            unset($data['post_ids']);

            // 创建用户
            $user = User::create($data);

            // 关联岗位
            if (!empty($postIds)) {
                $user->posts()->sync($postIds);
            }

            return $user;
        });
    }

    /**
     * 更新用户
     */
    public function update(int $id, array $data): User
    {
        return DB::transaction(function () use ($id, $data) {
            $user = User::findOrFail($id);

            // 提取岗位ID
            $postIds = $data['post_ids'] ?? null;
            unset($data['post_ids']);

            // 更新用户信息
            $user->fill($data);
            $user->save();

            // 更新岗位关联
            if ($postIds !== null) {
                $user->posts()->sync($postIds);
            }

            return $user;
        });
    }

    /**
     * 设置用户状态
     */
    public function setStatus(int $id, int $status): bool
    {
        $user = User::findOrFail($id);

        // 不能禁用超级管理员
        if ($user->is_super && $status === 0) {
            throw new \Exception('不能禁用超级管理员');
        }

        $user->status = $status;
        return $user->save();
    }

    /**
     * 重置密码
     */
    public function resetPassword(int $id): string
    {
        $user = User::findOrFail($id);

        // 重置为默认密码
        $user->password = AuthHelper::encryptPassword(AuthHelper::DEFAULT_PASSWORD);
        $user->salt = '';
        $user->save();

        return AuthHelper::DEFAULT_PASSWORD;
    }

    /**
     * 修改密码
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $user = User::findOrFail($id);

        $user->password = AuthHelper::encryptPassword($newPassword);
        $user->salt = '';
        return $user->save();
    }

    /**
     * 删除用户
     */
    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);

        // 不能删除超级管理员
        if ($user->is_super) {
            throw new \Exception('不能删除超级管理员');
        }

        // 删除用户岗位关联
        $user->posts()->detach();

        return $user->delete();
    }

    /**
     * 获取用户选项列表（用于下拉选择）
     */
    public function getOptions(array $params = []): array
    {
        $query = User::query()->where('status', 1);

        if (!empty($params['department_id'])) {
            $query->where('department_id', $params['department_id']);
        }

        $list = $query->select('id', 'username', 'name')
            ->orderBy('id', 'asc')
            ->get();

        return $list->map(fn($item) => [
            'id' => $item->id,
            'username' => $item->username,
            'name' => $item->name,
        ])->toArray();
    }

    /**
     * 获取用户权限列表
     */
    public function getPermissions(int $id): array
    {
        $user = User::with('posts')->findOrFail($id);

        if ($user->is_super) {
            return ['*'];
        }

        $permissions = [];
        foreach ($user->posts as $post) {
            if ($post->status !== 1) {
                continue;
            }

            $postPermissions = is_string($post->privilege)
                ? json_decode($post->privilege, true)
                : $post->privilege;

            if (is_array($postPermissions)) {
                $permissions = array_merge($permissions, $postPermissions);
            }
        }

        return array_unique($permissions);
    }

    /**
     * 更新用户权限（直接设置权限ID列表到岗位）
     */
    public function updatePermissions(int $id, array $permissionIds): bool
    {
        $user = User::findOrFail($id);

        if ($user->is_super) {
            throw new \Exception('超级管理员拥有所有权限，无需设置');
        }

        // 获取用户的主岗位
        $post = $user->posts()->first();

        if (!$post) {
            throw new \Exception('用户未关联岗位，请先设置岗位');
        }

        $post->privilege = $permissionIds;
        return $post->save();
    }
}
