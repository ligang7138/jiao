<?php

namespace App\Models\Complaint;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 投诉类型模型
 * 对应表: complaint_type
 */
class ComplaintType extends Model
{
    use SoftDeletes;

    protected $table = 'complaint_type';

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
    const STATUS_DISABLED = 0; // 禁用
    const STATUS_ENABLED = 1;  // 启用

    /**
     * 前台显示常量
     */
    const HOME_NO = 0;  // 不显示
    const HOME_YES = 1; // 显示

    /**
     * 获取状态文本
     */
    public function getStatusText(): string
    {
        $statusMap = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
        return $statusMap[$this->status] ?? '未知';
    }

    /**
     * 获取前台显示文本
     */
    public function getHomeText(): string
    {
        $homeMap = [
            self::HOME_NO => '不显示',
            self::HOME_YES => '显示',
        ];
        return $homeMap[$this->home] ?? '未知';
    }

    /**
     * 按状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        return $query;
    }

    /**
     * 按前台显示筛选
     */
    public function scopeByHome($query, $home)
    {
        if ($home !== null && $home !== '') {
            $query->where('home', $home);
        }
        return $query;
    }

    /**
     * 启用状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }

    /**
     * 前台显示
     */
    public function scopeShowInHome($query)
    {
        return $query->where('home', self::HOME_YES);
    }

    /**
     * 按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort', 'asc')->orderBy('id', 'desc');
    }

    /**
     * 是否启用
     */
    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }

    /**
     * 是否前台显示
     */
    public function isShowInHome(): bool
    {
        return $this->home === self::HOME_YES;
    }
}
