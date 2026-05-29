<?php

namespace App\Models\Bidding;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 报价日志模型
 * 对应表: discount_log
 */
class DiscountLog extends Model
{
    use SoftDeletes;

    protected $table = 'discount_log';

    protected $fillable = [
        'goods_id',
        'supp_id',
        'school_id',
        'canteen_id',
        'quotation_price',
        'limit_price',
        'discount',
        'remark',
    ];

    protected $casts = [
        'goods_id' => 'integer',
        'supp_id' => 'integer',
        'school_id' => 'integer',
        'canteen_id' => 'integer',
        'quotation_price' => 'decimal:2',
        'limit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联商品
     */
    public function goods()
    {
        return $this->belongsTo(\App\Models\Goods\Goods::class, 'goods_id');
    }

    /**
     * 关联供应商
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier\Supplier::class, 'supp_id');
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
     * 搜索作用域
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('goods', function ($sq) use ($keyword) {
                    $sq->where('goods_name', 'like', "%{$keyword}%");
                })->orWhereHas('supplier', function ($sq) use ($keyword) {
                    $sq->where('supplier_name', 'like', "%{$keyword}%");
                });
            });
        }
        return $query;
    }

    /**
     * 商品筛选
     */
    public function scopeByGoods($query, $goodsId)
    {
        if ($goodsId) {
            $query->where('goods_id', $goodsId);
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
}
