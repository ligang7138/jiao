<?php

namespace App\Models\Admin;

use App\Support\LegacyMenuBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * 管理员用户模型（兼容旧 user 表与新建 admin_users 表）
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory;

    /** 旧库 user 表无 created_at / updated_at */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('legacy.auth.user_table', 'user');
    }

    protected $fillable = [
        'username',
        'password',
        'salt',
        'name',
        'email',
        'mobile',
        'avatar',
        'department_id',
        'post',
        'is_super',
        'status',
        'last_login_time',
        'last_login_ip',
        'sso_user_id',
        'remark',
        'add_time',
    ];

    protected $hidden = [
        'password',
        'salt',
        'remember_token',
    ];

    protected $casts = [
        'status' => 'integer',
        'department_id' => 'integer',
        'sso_user_id' => 'integer',
        'add_time' => 'integer',
        'last_login_time' => 'integer',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'guard' => 'admin',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * 新架构多岗位关联（旧库无 pivot 时不会使用）
     */
    public function posts()
    {
        if (!Schema::hasTable('admin_user_posts')) {
            return $this->belongsToMany(Post::class, 'admin_user_posts', 'user_id', 'post_id')->whereRaw('1 = 0');
        }

        return $this->belongsToMany(Post::class, 'admin_user_posts', 'user_id', 'post_id');
    }

    public function getIsSuperAttribute($value): bool
    {
        if ($value !== null && $value !== '') {
            return (bool) $value;
        }

        $superUsernames = config('legacy.auth.super_usernames', []);

        return in_array($this->username, $superUsernames, true);
    }

    public function isSuper(): bool
    {
        return $this->getIsSuperAttribute($this->attributes['is_super'] ?? null);
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }

    /**
     * 旧权限码列表（system_menu.path），供中间件与路由过滤使用
     */
    public function getPrivilegeAttribute(): array
    {
        return LegacyMenuBuilder::resolvePrivilegePaths($this);
    }

    public function getPermissionsAttribute(): array
    {
        return LegacyMenuBuilder::resolvePrivilegePaths($this);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->privilege;

        if (in_array('*', $permissions, true)) {
            return true;
        }

        if (in_array($permission, $permissions, true)) {
            return true;
        }

        foreach ($permissions as $item) {
            if (is_string($item) && str_ends_with($item, '.*')) {
                $prefix = substr($item, 0, -2);
                if (str_starts_with($permission, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }
}
