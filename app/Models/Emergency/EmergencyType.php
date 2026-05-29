<?php

namespace App\Models\Emergency;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 应急类型模型
 * 对应表: emergency_type
 */
class EmergencyType extends Model
{
    use SoftDeletes;

    protected $table = 'emergency_type';

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
    const STATUS_DISABLED = 0;  // 禁用
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
     * 关联的应急事件
     */
    public function emergencies()
    {
        return $this->hasMany(Emergency::class, 'type_id');
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
    public function scopeShowOnHome($query)
    {
        return $query->where('home', self::HOME_YES);
    }

    /**
     * 获取启用的类型选项列表
     */
    public static function getOptions(): array
    {
        return self::enabled()
            ->orderBy('sort')
            ->get(['id', 'name'])
            ->map(fn($item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();
    }
}
