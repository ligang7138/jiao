<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 学校用户模型
 * 对应表: school_user
 */
class SchoolUser extends Model
{
    use SoftDeletes;

    protected $table = 'school_user';

    protected $fillable = [
        'school_id',
        'username',
        'password',
        'salt',
        'realname',
        'phone',
        'email',
        'status',
        'remark',
    ];

    protected $casts = [
        'school_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'password',
        'salt',
    ];

    /**
     * 所属学校
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
}