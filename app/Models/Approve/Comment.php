<?php

namespace App\Models\Approve;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 评论模型
 * 对应表: comment
 */
class Comment extends Model
{
    use SoftDeletes;

    protected $table = 'comment';

    protected $fillable = [
        'order_id',
        'content',
        'images',
        'user_id',
        'user_name',
        'school_id',
        'canteen_id',
        'service_score',
        'delivery_score',
        'quality_score',
        'price_score',
        'review_status',
        'review_time',
        'review_user_id',
    ];

    protected $casts = [
        'images' => 'array',
        'service_score' => 'integer',
        'delivery_score' => 'integer',
        'quality_score' => 'integer',
        'price_score' => 'integer',
        'review_status' => 'integer',
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
     * 审阅人
     */
    public function reviewer()
    {
        return $this->belongsTo(\App\Models\Admin\AdminUser::class, 'review_user_id');
    }

    /**
     * 评价人
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\Sso\SsoUser::class, 'user_id');
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
