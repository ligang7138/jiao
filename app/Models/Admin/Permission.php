<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 权限模型
 */
class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';

    protected $fillable = [
        'parent_id',
        'name',
        'code',
        'type',
        'path',
        'component',
        'icon',
        'sort',
        'status',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'type' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 类型常量
    const TYPE_MENU = 1;    // 菜单
    const TYPE_BUTTON = 2;  // 按钮

    /**
     * 父权限
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 子权限
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * 关联的角色
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }

    /**
     * 是否为菜单类型
     */
    public function isMenu(): bool
    {
        return $this->type === self::TYPE_MENU;
    }

    /**
     * 是否启用
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    /**
     * 获取树形结构
     */
    public static function getTree(): array
    {
        $permissions = self::orderBy('sort')
            ->orderBy('id')
            ->get()
            ->toArray();

        return self::buildTree($permissions);
    }

    /**
     * 构建树形结构
     */
    private static function buildTree(array $data, int $parentId = 0): array
    {
        $tree = [];

        foreach ($data as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = self::buildTree($data, $item['id']);
                if ($children) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }

        return $tree;
    }
}
