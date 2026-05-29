<?php

namespace App\Models\Order;

use App\Constants\OrderStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 订单模型
 * 对应表: orders
 */
class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected static function newFactory()
    {
        return \Database\Factories\OrderFactory::new();
    }

    protected $fillable = [
        'order_sn',
        'canteen_id',
        'supp_id',
        'school_id',
        'status',
        'order_type',
        'replenish_type_id',
        'audit_status',
        'audit_time',
        'audit_user_type',
        'send_date',
        'total_price',
        'send_price',
        'receive_price',
        'back_price',
        'settle_price',
        'is_send_late',
        'inspection_report',
        'remark',
        'add_time',
        'update_time',
    ];

    protected $casts = [
        'canteen_id' => 'integer',
        'supp_id' => 'integer',
        'school_id' => 'integer',
        'status' => 'integer',
        'order_type' => 'integer',
        'replenish_type_id' => 'integer',
        'audit_status' => 'integer',
        'audit_time' => 'integer',
        'audit_user_type' => 'integer',
        'send_date' => 'date',
        'total_price' => 'decimal:2',
        'send_price' => 'decimal:2',
        'receive_price' => 'decimal:2',
        'back_price' => 'decimal:2',
        'settle_price' => 'decimal:2',
        'is_send_late' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取状态文本（与旧系统 OrderStatus 一致）
     */
    public function getStatusText(): string
    {
        return OrderStatus::getName((int) $this->status);
    }

    /**
     * 订单商品
     */
    public function goods()
    {
        return $this->hasMany(OrderGoods::class, 'order_id');
    }

    /**
     * 所属学校
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School\School::class, 'school_id');
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
     * 搜索作用域
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where('order_sn', 'like', "%{$keyword}%");
        }
        return $query;
    }

    /**
     * 学校筛选
     */
    public function scopeBySchool($query, $schoolId)
    {
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return $query;
    }

    /**
     * 订单编号（精确匹配，与旧系统一致）
     */
    public function scopeByOrderSn($query, $orderSn)
    {
        if ($orderSn !== null && $orderSn !== '') {
            $query->where('order_sn', $orderSn);
        }

        return $query;
    }

    /**
     * 食堂名称模糊搜索
     */
    public function scopeByCanteenName($query, $canteenName)
    {
        if ($canteenName !== null && $canteenName !== '') {
            $query->where('canteen_name', 'like', '%' . $canteenName . '%');
        }

        return $query;
    }

    /**
     * 食堂类型
     */
    public function scopeByCanteenType($query, $canteenType)
    {
        if ($canteenType !== null && $canteenType !== '') {
            $query->where('canteen_type', (int) $canteenType);
        }

        return $query;
    }

    /**
     * 订单类型
     */
    public function scopeByOrderType($query, $orderType)
    {
        if ($orderType !== null && $orderType !== '') {
            $query->where('order_type', (int) $orderType);
        }

        return $query;
    }

    /**
     * 状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        return $query;
    }

    /**
     * 主账号审核状态（-1 表示全部，不筛选）
     */
    public function scopeByAuditStatus($query, $auditStatus)
    {
        if ($auditStatus !== null && $auditStatus !== '' && (int) $auditStatus > -1) {
            $query->where('audit_status', (int) $auditStatus);
        }

        return $query;
    }

    /**
     * 是否迟到（-1 表示全部）
     */
    public function scopeByIsSendLate($query, $isSendLate)
    {
        if ($isSendLate !== null && $isSendLate !== '' && (int) $isSendLate > -1) {
            $query->where('is_send_late', (int) $isSendLate);
        }

        return $query;
    }

    /**
     * 索票索证状态（-1 表示全部）
     */
    public function scopeByInspectionReportStatus($query, $status)
    {
        if ($status !== null && $status !== '' && (int) $status > -1) {
            $query->where('inspection_report_status', (int) $status);
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
     * 后台订单列表筛选（与 admin/order/index.php 一致）
     */
    public function scopeApplyAdminListFilters($query, array $params)
    {
        $orderSn = $params['order_sn'] ?? null;

        $query->byOrderSn($orderSn);

        if ($orderSn) {
            return $query;
        }

        $dateType = (int) ($params['date_type'] ?? 1);
        $startDate = self::normalizeDateValue($params['start_date'] ?? null);
        $endDate = self::normalizeDateValue($params['end_date'] ?? null);

        if ($dateType === 1) {
            if ($startDate) {
                $query->where('send_date', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('send_date', '<=', $endDate);
            }
        } else {
            if ($startDate) {
                $query->where('add_time', '>=', strtotime($startDate));
            }
            if ($endDate) {
                $query->where('add_time', '<=', strtotime($endDate . ' 23:59:59'));
            }
        }

        return $query
            ->byCanteenName($params['canteen_name'] ?? null)
            ->byCanteenType($params['canteen_type'] ?? null)
            ->byOrderType($params['order_type'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->bySupplier($params['supp_id'] ?? $params['supplier_id'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->byAuditStatus($params['audit_status'] ?? null)
            ->byIsSendLate($params['is_send_late'] ?? null)
            ->byInspectionReportStatus($params['inspection_report_status'] ?? null);
    }

    /**
     * 日期范围筛选（默认按送货日期，导出等场景使用）
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        $startDate = self::normalizeDateValue($startDate);
        $endDate = self::normalizeDateValue($endDate);

        if ($startDate) {
            $query->where('send_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('send_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * 将 ISO8601 / 时间戳等统一为 Y-m-d
     */
    public static function normalizeDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return date('Y-m-d', (int) $value);
        }

        $value = (string) $value;

        if (str_contains($value, 'T')) {
            return Carbon::parse($value)->format('Y-m-d');
        }

        return substr($value, 0, 10);
    }
}