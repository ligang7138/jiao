<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 岗位模型（兼容旧 post 表）
 */
class Post extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('legacy.auth.post_table', 'post');
    }

    protected $fillable = [
        'name',
        'department_id',
        'remark',
        'privilege',
        'status',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'status' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'admin_user_posts', 'post_id', 'user_id');
    }

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }
}
