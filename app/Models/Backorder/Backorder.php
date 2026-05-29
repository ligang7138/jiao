<?php

namespace App\Models\Backorder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 退货单模型
 * 对应表: backorder
 */
class Backorder extends Model
{
    use SoftDeletes;

    protected $table = 'backorder';

    protected $fillable = [
        'order_id',
        'order_goods_id',
        'quantity',
        'type',
        'status',
        'reason',
        'reason_type_id',
        'solution',
        'remark',
        'audit_user_id',
        'audit_time',
        'cancel_user_id',
        'cancel_time',
        'cancel_reason',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'order_goods_id' => 'integer',
        'quantity' => 'decimal:2',
        'type' => 'integer',
        'status' => 'integer',
        'reason_type_id' => 'integer',
        'audit_user_id' => 'integer',
        'audit_time' => 'datetime',
        'cancel_user_id' => 'integer',
        'cancel_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 退货单状态常量
     */
    const STATUS_CANCELLED = 1;      // 取消
    const STATUS_REJECTED = 2;       // 审核拒绝
    const STATUS_PENDING = 3;        // 待审核
    const STATUS_APPROVED = 4;       // 通过

    /**
     * 退货类型常量
     */
    const TYPE_REFUND_ONLY = 1;      // 仅退款
    const TYPE_REFUND_RETURN = 2;    // 退货退款

    /**
     * 获取状态文本
     */
    public function getStatusText(): string
    {
        $statusMap = [
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_REJECTED => '审核拒绝',
            self::STATUS_PENDING => '待审核',
            self::STATUS_APPROVED => '已通过',
        ];
        return $statusMap[$this->status] ?? '未知';
    }

    /**
     * 获取类型文本
     */
    public function getTypeText(): string
    {
        $typeMap = [
            self::TYPE_REFUND_ONLY => '仅退款',
            self::TYPE_REFUND_RETURN => '退货退款',
        ];
        return $typeMap[$this->type] ?? '未知';
    }

    /**
     * 关联订单
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Order::class, 'order_id');
    }

    /**
     * 关联订单商品
     */
    public function orderGoods()
    {
        return $this->belongsTo(\App\Models\Order\OrderGoods::class, 'order_goods_id');
    }

    /**
     * 关联退货原因类型
     */
    public function reasonType()
    {
        return $this->belongsTo(BackorderType::class, 'reason_type_id');
    }

    /**
     * 审核人
     */
    public function auditor()
    {
        return $this->belongsTo(\App\Models\User\AdminUser::class, 'audit_user_id');
    }

    /**
     * 取消人
     */
    public function canceller()
    {
        return $this->belongsTo(\App\Models\User\AdminUser::class, 'cancel_user_id');
    }

    /**
     * 搜索作用域
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->whereHas('order', function ($q) use ($keyword) {
                $q->where('order_no', 'like', "%{$keyword}%");
            });
        }
        return $query;
    }

    /**
     * 状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        return $query;
    }

    /**
     * 类型筛选
     */
    public function scopeByType($query, $type)
    {
        if ($type !== null && $type !== '') {
            $query->where('type', $type);
        }
        return $query;
    }

    /**
     * 日期范围筛选
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
     * 待审核状态
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * 是否可审核
     */
    public function canAudit(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 是否可取消
     */
    public function canCancel(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
