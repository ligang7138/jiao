<?php

namespace App\Models\Emergency;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 应急事件模型
 * 对应表: emergency
 */
class Emergency extends Model
{
    use SoftDeletes;

    protected $table = 'emergency';

    protected $fillable = [
        'canteen_id',
        'type_id',
        'type_name',
        'linkman',
        'mobile',
        'content',
        'logo',
        'add_time',
        'process_status',
        'process_remark',
        'process_user',
        'process_time',
    ];

    protected $casts = [
        'canteen_id' => 'integer',
        'type_id' => 'integer',
        'process_status' => 'integer',
        'add_time' => 'datetime',
        'process_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 处理状态常量
     */
    const STATUS_PENDING = 0;   // 未处理
    const STATUS_PROCESSED = 1; // 已处理

    /**
     * 获取处理状态文本
     */
    public function getStatusText(): string
    {
        $statusMap = [
            self::STATUS_PENDING => '未处理',
            self::STATUS_PROCESSED => '已处理',
        ];
        return $statusMap[$this->process_status] ?? '未知';
    }

    /**
     * 所属应急类型
     */
    public function type()
    {
        return $this->belongsTo(EmergencyType::class, 'type_id');
    }

    /**
     * 所属食堂（关联学校）
     */
    public function canteen()
    {
        return $this->belongsTo(\App\Models\School\Canteen::class, 'canteen_id');
    }

    /**
     * 处理人
     */
    public function processor()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'process_user');
    }

    /**
     * 按处理状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('process_status', $status);
        }
        return $query;
    }

    /**
     * 按应急类型筛选
     */
    public function scopeByType($query, $typeId)
    {
        if ($typeId) {
            $query->where('type_id', $typeId);
        }
        return $query;
    }

    /**
     * 按食堂筛选
     */
    public function scopeByCanteen($query, $canteenId)
    {
        if ($canteenId) {
            $query->where('canteen_id', $canteenId);
        }
        return $query;
    }

    /**
     * 关键词搜索
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('linkman', 'like', "%{$keyword}%")
                    ->orWhere('mobile', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }

    /**
     * 时间范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('add_time', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('add_time', '<=', $endDate);
        }
        return $query;
    }
}
