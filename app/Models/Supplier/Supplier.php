<?php

namespace App\Models\Supplier;

use App\Models\Bidding\BiddingLog;
use App\Models\Goods\GoodsPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 供应商模型
 * 对应表: supplier
 */
class Supplier extends Model
{
    use HasFactory;

    /**
     * 旧库 supplier 表无 deleted_at，使用 status 字段管理状态
     */
    public $timestamps = false;

    protected $table = 'supplier';

    /** 停用 */
    public const STATUS_DISABLED = 0;

    /** 启用 */
    public const STATUS_ENABLED = 1;

    protected static function newFactory()
    {
        return \Database\Factories\SupplierFactory::new();
    }

    protected $fillable = [
        'username',
        'code',
        'name',
        'company',
        'address',
        'cate_type',
        'cate_ids',
        'license_logo',
        'credit_code',
        'permit_logo',
        'permit_code',
        'linkman',
        'mobile',
        'emergency_linkman',
        'emergency_mobile',
        'sso_user_id',
        'score',
        'comment_score',
        'status',
        'add_time',
        'update_time',
    ];

    protected $casts = [
        'cate_type' => 'integer',
        'score' => 'decimal:2',
        'comment_score' => 'decimal:2',
        'status' => 'integer',
        'sso_user_id' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
    ];

    public function goodsPrices(): HasMany
    {
        return $this->hasMany(GoodsPrice::class, 'supp_id');
    }

    public function biddingLogs(): HasMany
    {
        return $this->hasMany(BiddingLog::class, 'supp_id');
    }

    public function discountLogs(): HasMany
    {
        return $this->hasMany(DiscountLog::class, 'supp_id');
    }

    /**
     * 兼容旧代码中 goods() 调用，实际关联 goods_price
     */
    public function goods(): HasMany
    {
        return $this->goodsPrices();
    }

    public function getStatusText(): string
    {
        return match ((int) $this->status) {
            self::STATUS_DISABLED => '停用',
            self::STATUS_ENABLED => '启用',
            default => '未知',
        };
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword === null || $keyword === '') {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('linkman', 'like', "%{$keyword}%")
                ->orWhere('mobile', 'like', "%{$keyword}%")
                ->orWhere('code', 'like', "%{$keyword}%")
                ->orWhere('username', 'like', "%{$keyword}%");
        });
    }

    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        return $query;
    }

    public function scopeByCode($query, ?string $code)
    {
        if ($code !== null && $code !== '') {
            $query->where('code', 'like', '%' . $code . '%');
        }

        return $query;
    }

    public function scopeByUsername($query, ?string $username)
    {
        if ($username !== null && $username !== '') {
            $query->where('username', 'like', '%' . $username . '%');
        }

        return $query;
    }

    public function scopeByCateType($query, $cateType)
    {
        if ($cateType !== null && $cateType !== '') {
            $query->where('cate_type', (int) $cateType);
        }

        return $query;
    }
}
