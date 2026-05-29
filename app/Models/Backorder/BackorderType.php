<?php

namespace App\Models\Backorder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 退货原因类型模型
 * 对应表: backorder_type
 */
class BackorderType extends Model
{
    use SoftDeletes;

    protected $table = 'backorder_type';

    protected $fillable = [
        'name',
        'home',
        'sort',
        'status',
    ];

    protected $casts = [
        'home' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;  // 停用
    const STATUS_ENABLED = 1;   // 启用

    /**
     * 前台显示常量
     */
    const HOME_NO = 0;   // 不显示
    const HOME_YES = 1;  // 显示

    /**
     * 获取状态文本
     */
    public function getStatusText(): string
    {
        return $this->status === self::STATUS_ENABLED ? '启用' : '停用';
    }

    /**
     * 获取前台显示文本
     */
    public function getHomeText(): string
    {
        return $this->home === self::HOME_YES ? '显示' : '不显示';
    }

    /**
     * 启用状态作用域
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 前台显示作用域
     */
    public function scopeShowInHome($query)
    {
        return $query->where('home', self::HOME_YES);
    }

    /**
     * 按排序作用域
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('id', 'desc');
    }

    /**
     * 关联退货单
     */
    public function backorders()
    {
        return $this->hasMany(Backorder::class, 'reason_type_id');
    }
}
