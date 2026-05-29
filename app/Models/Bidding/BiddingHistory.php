<?php

namespace App\Models\Bidding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 合作申请历史模型
 * 对应表: bidding_history
 */
class BiddingHistory extends Model
{
    use SoftDeletes;

    protected $table = 'bidding_history';

    protected $fillable = [
        'canteen_id',
        'supp_id',
        'school_id',
        'type',
        'audit_status',
        'audit_time',
        'auditor_id',
        'audit_remark',
        'start_date',
        'end_date',
        'attachments',
        'remark',
        'review_status',
        'review_time',
        'review_user_id',
        'emergency_contact',
        'emergency_phone',
    ];

    protected $casts = [
        'canteen_id' => 'integer',
        'supp_id' => 'integer',
        'school_id' => 'integer',
        'type' => 'integer',
        'audit_status' => 'integer',
        'audit_time' => 'datetime',
        'auditor_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'attachments' => 'array',
        'review_status' => 'integer',
        'review_time' => 'datetime',
        'review_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 申请类型常量
     */
    const TYPE_APPLY = 1;     // 新申请
    const TYPE_RENEW = 2;     // 续约
    const TYPE_TERMINATE = 3; // 终止

    /**
     * 审核状态常量
     */
    const AUDIT_STATUS_PENDING = 1;  // 待审核
    const AUDIT_STATUS_REJECTED = 2; // 已拒绝
    const AUDIT_STATUS_APPROVED = 3; // 已通过

    /**
     * 审阅状态常量
     */
    const REVIEW_STATUS_PENDING = 0;  // 未审阅
    const REVIEW_STATUS_REVIEWED = 1; // 已审阅

    /**
     * 获取申请类型文本
     */
    public function getTypeText(): string
    {
        $typeMap = [
            self::TYPE_APPLY => '新申请',
            self::TYPE_RENEW => '续约',
            self::TYPE_TERMINATE => '终止',
        ];
        return $typeMap[$this->type] ?? '未知';
    }

    /**
     * 获取审核状态文本
     */
    public function getAuditStatusText(): string
    {
        $statusMap = [
            self::AUDIT_STATUS_PENDING => '待审核',
            self::AUDIT_STATUS_REJECTED => '已拒绝',
            self::AUDIT_STATUS_APPROVED => '已通过',
        ];
        return $statusMap[$this->audit_status] ?? '未知';
    }

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
     * 所属食堂
     */
    public function canteen()
    {
        return $this->belongsTo(\App\Models\School\Canteen::class, 'canteen_id');
    }

    /**
     * 所属学校
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School\School::class, 'school_id');
    }

    /**
     * 供应商
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier\Supplier::class, 'supp_id');
    }

    /**
     * 审核人
     */
    public function auditor()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'auditor_id');
    }

    /**
     * 审阅人
     */
    public function reviewer()
    {
        return $this->belongsTo(\App\Models\Admin\Admin::class, 'review_user_id');
    }

    /**
     * 搜索作用域
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('school', function ($sq) use ($keyword) {
                    $sq->where('school_name', 'like', "%{$keyword}%");
                })->orWhereHas('supplier', function ($sq) use ($keyword) {
                    $sq->where('supplier_name', 'like', "%{$keyword}%");
                });
            });
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
     * 审核状态筛选
     */
    public function scopeByAuditStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('audit_status', $status);
        }
        return $query;
    }

    /**
     * 申请类型筛选
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
     * 待审核
     */
    public function scopePending($query)
    {
        return $query->where('audit_status', self::AUDIT_STATUS_PENDING);
    }

    /**
     * 已审核（通过或拒绝）
     */
    public function scopeAudited($query)
    {
        return $query->whereIn('audit_status', [self::AUDIT_STATUS_APPROVED, self::AUDIT_STATUS_REJECTED]);
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
     * 按紧急联系人筛选
     */
    public function scopeByEmergencyContact($query, $contact)
    {
        if ($contact) {
            $query->where('emergency_contact', 'like', "%{$contact}%");
        }
        return $query;
    }

    /**
     * 按紧急联系电话筛选
     */
    public function scopeByEmergencyPhone($query, $phone)
    {
        if ($phone) {
            $query->where('emergency_phone', 'like', "%{$phone}%");
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

    /**
     * 获取附件完整URL
     */
    public function getAttachmentUrls(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        $urls = [];
        $uploadUrl = config('app.upload_url', '');
        foreach ($this->attachments as $attachment) {
            $urls[] = $uploadUrl . $attachment;
        }
        return $urls;
    }
}
