<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 应收账款-对账单模型
 * 对应表: receivable_receipt
 */
class ReceivableReceipt extends Model
{
    use SoftDeletes;

    protected $table = 'receivable_receipt';

    protected $fillable = [
        'voucher_sn',
        'canteen_id',
        'supp_id',
        'debit_price',
        'credit_price',
        'invoice_status',
        'bill_status',
        'school_confirm_status',
        'invoice_time',
        'bill_time',
        'remark',
    ];

    protected $casts = [
        'canteen_id' => 'integer',
        'supp_id' => 'integer',
        'debit_price' => 'decimal:2',
        'credit_price' => 'decimal:2',
        'invoice_status' => 'integer',
        'bill_status' => 'integer',
        'school_confirm_status' => 'integer',
        'invoice_time' => 'datetime',
        'bill_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 开票状态常量
     */
    const INVOICE_STATUS_PENDING = 0;   // 待开票
    const INVOICE_STATUS_PARTIAL = 1;   // 部分开票
    const INVOICE_STATUS_COMPLETED = 2; // 已开票

    /**
     * 收款状态常量
     */
    const BILL_STATUS_PENDING = 0;      // 待收款
    const BILL_STATUS_PARTIAL = 1;      // 部分收款
    const BILL_STATUS_COMPLETED = 2;    // 已收款

    /**
     * 学校确认状态常量
     */
    const SCHOOL_CONFIRM_PENDING = 0;   // 待确认
    const SCHOOL_CONFIRM_APPROVED = 1;  // 已确认
    const SCHOOL_CONFIRM_REJECTED = 2;  // 已驳回

    /**
     * 获取开票状态文本
     */
    public function getInvoiceStatusText(): string
    {
        $statusMap = [
            self::INVOICE_STATUS_PENDING => '待开票',
            self::INVOICE_STATUS_PARTIAL => '部分开票',
            self::INVOICE_STATUS_COMPLETED => '已开票',
        ];
        return $statusMap[$this->invoice_status] ?? '未知';
    }

    /**
     * 获取收款状态文本
     */
    public function getBillStatusText(): string
    {
        $statusMap = [
            self::BILL_STATUS_PENDING => '待收款',
            self::BILL_STATUS_PARTIAL => '部分收款',
            self::BILL_STATUS_COMPLETED => '已收款',
        ];
        return $statusMap[$this->bill_status] ?? '未知';
    }

    /**
     * 获取学校确认状态文本
     */
    public function getSchoolConfirmStatusText(): string
    {
        $statusMap = [
            self::SCHOOL_CONFIRM_PENDING => '待确认',
            self::SCHOOL_CONFIRM_APPROVED => '已确认',
            self::SCHOOL_CONFIRM_REJECTED => '已驳回',
        ];
        return $statusMap[$this->school_confirm_status] ?? '未知';
    }

    /**
     * 所属食堂
     */
    public function canteen()
    {
        return $this->belongsTo(\App\Models\School\Canteen::class, 'canteen_id');
    }

    /**
     * 供应商
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier\Supplier::class, 'supp_id');
    }

    /**
     * 账单明细
     */
    public function accounts()
    {
        return $this->hasMany(ReceivableAccount::class, 'receipt_id');
    }

    /**
     * 搜索作用域
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where('voucher_sn', 'like', "%{$keyword}%");
        }
        return $query;
    }

    /**
     * 食堂筛选
     */
    public function scopeByCanteen($query, $canteenId)
    {
        if ($canteenId) {
            $query->where('canteen_id', $canteenId);
        }
        return $query;
    }

    /**
     * 供应商筛选
     */
    public function scopeBySupplier($query, $supplierId)
    {
        if ($supplierId) {
            $query->where('supp_id', $supplierId);
        }
        return $query;
    }

    /**
     * 开票状态筛选
     */
    public function scopeByInvoiceStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('invoice_status', $status);
        }
        return $query;
    }

    /**
     * 收款状态筛选
     */
    public function scopeByBillStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('bill_status', $status);
        }
        return $query;
    }

    /**
     * 学校确认状态筛选
     */
    public function scopeBySchoolConfirmStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('school_confirm_status', $status);
        }
        return $query;
    }

    /**
     * 日期范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate . ' 23:59:59');
        }
        return $query;
    }

    /**
     * 计算未开票金额
     */
    public function getUninvoicedAmount(): float
    {
        return max(0, $this->debit_price - $this->credit_price);
    }

    /**
     * 计算未收款金额
     */
    public function getUnbilledAmount(): float
    {
        return max(0, $this->credit_price - $this->getBilledAmount());
    }

    /**
     * 获取已收款金额
     */
    public function getBilledAmount(): float
    {
        return $this->accounts()
            ->where('type', ReceivableAccount::TYPE_BILL)
            ->where('status', ReceivableAccount::STATUS_ENABLED)
            ->sum('price');
    }
}
