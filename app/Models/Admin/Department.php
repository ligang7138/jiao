<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 部门模型
 */
class Department extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('legacy.auth.department_table', 'department');
    }

    protected $fillable = [
        'name',
        'parent_id',
        'sort',
        'status',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 父部门
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * 子部门
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * 部门下的岗位
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'department_id');
    }

    /**
     * 部门下的用户
     */
    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * 是否启用
     */
    public function isActive(): bool
    {
        return $this->status === 1;
    }
}
