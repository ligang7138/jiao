<?php

namespace App\Models\Approve;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 投诉模型
 * 对应表: complaint
 */
class Complaint extends Model
{
    use SoftDeletes;

    protected $table = 'complaint';

    protected $fillable = [
        'order_id',
        'type_id',
        'type_name',
        'content',
        'images',
        'user_id',
        'user_name',
        'contact_name',
        'contact_phone',
        'school_id',
        'canteen_id',
        'supplier_id',
        'process_status',
        'process_time',
        'process_user_id',
        'process_remark',
        'review_status',
        'review_time',
        'review_user_id',
    ];

    protected $casts = [
        'images' => 'array',
        'type_id' => 'integer',
        'process_status' => 'integer',
        'review_status' => 'integer',
        'process_time' => 'datetime',
        'review_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 审阅状态常量
     */
    const REVIEW_STATUS_PENDING = 0;  // 未审阅
    const REVIEW_STATUS_REVIEWED = 1; // 已审阅

    /**
     * 处理状态常量
     */
    const PROCESS_STATUS_PENDING = 0;  // 未处理
    const PROCESS_STATUS_PROCESSING = 1; // 处理中
    const PROCESS_STATUS_COMPLETED = 2;  // 已处理

    /**
     * 获取审阅状态文本
     */
    public function getReviewStatusText(): string
    {
        $statusMap = [
            self::REVIEW_STATUS_PENDING => '未审阅',
            self::REVIEW_STATUS_REVIEWED => '已审阅',
        ];
        return $statusMap[$this->review_status] ?? '未知';
    }

    /**
     * 获取处理状态文本
     */
    public function getProcessStatusText(): string
    {
        $statusMap = [
            self::PROCESS_STATUS_PENDING => '未处理',
            self::PROCESS_STATUS_PROCESSING => '处理中',
            self::PROCESS_STATUS_COMPLETED => '已处理',
        ];
        return $statusMap[$this->process_status] ?? '未知';
    }

    /**
     * 关联订单
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Order::class, 'order_id');
    }

    /**
     * 关联学校
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School\School::class, 'school_id');
    }

    /**
     * 关联食堂
     */
    public function canteen()
    {
        return $this->belongsTo(\App\Models\School\Canteen::class, 'canteen_id');
    }

    /**
     * 关联供应商
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier\Supplier::class, 'supplier_id');
    }

    /**
     * 审阅人
     */
    public function reviewer()
    {
        return $this->belongsTo(\App\Models\Admin\AdminUser::class, 'review_user_id');
    }

    /**
     * 处理人
     */
    public function processor()
    {
        return $this->belongsTo(\App\Models\Admin\AdminUser::class, 'process_user_id');
    }

    /**
     * 按审阅状态筛选
     */
    public function scopeByReviewStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('review_status', $status);
        }
        return $query;
    }

    /**
     * 按处理状态筛选
     */
    public function scopeByProcessStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('process_status', $status);
        }
        return $query;
    }

    /**
     * 按学校名称筛选
     */
    public function scopeByCanteenName($query, $canteenName)
    {
        if ($canteenName) {
            $query->whereHas('canteen', function ($q) use ($canteenName) {
                $q->where('canteen_name', 'like', "%{$canteenName}%");
            });
        }
        return $query;
    }

    /**
     * 按联系人筛选
     */
    public function scopeByContactName($query, $contactName)
    {
        if ($contactName) {
            $query->where('contact_name', 'like', "%{$contactName}%");
        }
        return $query;
    }

    /**
     * 按联系电话筛选
     */
    public function scopeByContactPhone($query, $contactPhone)
    {
        if ($contactPhone) {
            $query->where('contact_phone', 'like', "%{$contactPhone}%");
        }
        return $query;
    }

    /**
     * 按日期范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        return $query;
    }

    /**
     * 是否已审阅
     */
    public function isReviewed(): bool
    {
        return $this->review_status === self::REVIEW_STATUS_REVIEWED;
    }
}
