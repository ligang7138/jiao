<?php

namespace App\Models\School;

use App\Constants\CanteenType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 食堂模型
 * 对应表: school_canteen
 */
class Canteen extends Model
{
    use HasFactory;

    protected $table = 'school_canteen';

    public $timestamps = false;

    protected static function newFactory()
    {
        return \Database\Factories\CanteenFactory::new();
    }

    protected $fillable = [
        'school_id',
        'username',
        'password',
        'name',
        'canteen_sn',
        'canteen_type',
        'linkman',
        'mobile',
        'receive_linkman',
        'receive_mobile',
        'receive_start_time',
        'receive_end_time',
        'emergency_linkman',
        'emergency_mobile',
        'code',
        'credit_code',
        'address',
        'remark',
        'monthly_purchase_amount',
        'current_month_purchase',
        'group_id',
        'is_audit',
        'bank_no',
        'taxpayer_no',
        'invoice_title',
        'invoice_phone',
        'invoice_address',
        'account_create_type',
        'account_type',
        'account_period',
        'account_time',
        'account_start_date',
        'account_end_date',
        'account_execute_date',
        'status',
        'add_user',
        'add_time',
        'update_time',
    ];

    protected $casts = [
        'school_id' => 'integer',
        'canteen_type' => 'integer',
        'monthly_purchase_amount' => 'decimal:2',
        'current_month_purchase' => 'decimal:2',
        'group_id' => 'integer',
        'is_audit' => 'integer',
        'account_type' => 'integer',
        'account_period' => 'integer',
        'account_time' => 'integer',
        'account_create_type' => 'integer',
        'status' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    public function getStatusText(): string
    {
        return match ((int) $this->status) {
            self::STATUS_ENABLED => '启用',
            self::STATUS_DISABLED => '停用',
            default => '未知',
        };
    }

    public function getCanteenTypeText(): string
    {
        return CanteenType::getName((int) $this->canteen_type);
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    public function group()
    {
        return $this->belongsTo(\App\Models\Group\Group::class, 'group_id');
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        return $query;
    }

    public function scopeBySchool($query, $schoolId)
    {
        if ($schoolId) {
            $query->where('school_id', (int) $schoolId);
        }

        return $query;
    }

    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        return $query;
    }
}
