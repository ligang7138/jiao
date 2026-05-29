<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 角色模型
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 角色的权限
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * 关联的岗位
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_roles', 'role_id', 'post_id');
    }

    /**
     * 是否启用
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }
}
