<?php

namespace App\Models\Jiagewang;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品指导价模型
 * 对应表: goods_jiagewang
 */
class GoodsJiagewang extends Model
{
    protected $table = 'goods_jiagewang';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'goods_sn',
        'name',
        'cate_name',
        'scate_name',
        'price',
        'update_date',
        'update_user',
        'update_time',
    ];

    protected $casts = [
        'goods_id' => 'integer',
        'price' => 'decimal:2',
        'update_time' => 'integer',
    ];

    /**
     * 关联商品
     */
    public function goods()
    {
        return $this->belongsTo(\App\Models\Goods\Goods::class, 'goods_id');
    }

    /**
     * 按商品编码搜索
     */
    public function scopeByGoodsSn($query, $goodsSn)
    {
        if ($goodsSn) {
            $query->where('goods_sn', $goodsSn);
        }
        return $query;
    }

    /**
     * 按商品名称搜索
     */
    public function scopeByGoodsName($query, $goodsName)
    {
        if ($goodsName) {
            $query->where('name', 'like', "%{$goodsName}%");
        }
        return $query;
    }

    /**
     * 按一级分类筛选
     */
    public function scopeByCateName($query, $cateName)
    {
        if ($cateName) {
            $query->where('cate_name', $cateName);
        }
        return $query;
    }

    /**
     * 按二级分类筛选
     */
    public function scopeByScateName($query, $scateName)
    {
        if ($scateName) {
            $query->where('scate_name', $scateName);
        }
        return $query;
    }

    /**
     * 按更新日期范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->where('update_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('update_date', '<=', $endDate);
        }
        return $query;
    }
}
