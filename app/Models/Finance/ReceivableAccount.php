<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 应收账款-账单明细模型
 * 对应表: receivable_account
 */
class ReceivableAccount extends Model
{
    use SoftDeletes;

    protected $table = 'receivable_account';

    protected $fillable = [
        'receipt_id',
        'order_id',
        'type',
        'price',
        'status',
        'remark',
    ];

    protected $casts = [
        'receipt_id' => 'integer',
        'order_id' => 'integer',
        'type' => 'integer',
        'price' => 'decimal:2',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 类型常量
     */
    const TYPE_DEBIT = 1;   // 借方（应收）
    const TYPE_CREDIT = 2;  // 贷方（应付）
    const TYPE_INVOICE = 3; // 开票
    const TYPE_BILL = 4;    // 收款

    /**
     * 状态常量
     */
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用

    /**
     * 获取类型文本
     */
    public function getTypeText(): string
    {
        $typeMap = [
            self::TYPE_DEBIT => '应收',
            self::TYPE_CREDIT => '应付',
            self::TYPE_INVOICE => '开票',
            self::TYPE_BILL => '收款',
        ];
        return $typeMap[$this->type] ?? '未知';
    }

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
     * 所属对账单
     */
    public function receipt()
    {
        return $this->belongsTo(ReceivableReceipt::class, 'receipt_id');
    }

    /**
     * 关联订单
     */
    public function order()
    {
        return $this->belongsTo(\App\Models\Order\Order::class, 'order_id');
    }

    /**
     * 对账单筛选
     */
    public function scopeByReceipt($query, $receiptId)
    {
        if ($receiptId) {
            $query->where('receipt_id', $receiptId);
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
     * 启用状态
     */
    public function scopeEnabled($query)
    {
        return $query->where('status', self::STATUS_ENABLED);
    }
}
