<?php

namespace App\Models\Group;

use Illuminate\Database\Eloquent\Model;

/**
 * 分组模型
 * 对应表: `group`
 */
class Group extends Model
{
    protected $table = 'group';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'pid',
        'code',
        'status',
        'add_user',
        'add_time',
        'update_user',
        'update_time',
    ];

    protected $casts = [
        'pid' => 'integer',
        'status' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    public function parent()
    {
        return $this->belongsTo(self::class, 'pid');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'pid');
    }

    public function canteens()
    {
        return $this->hasMany(\App\Models\School\Canteen::class, 'group_id');
    }
}
