<?php

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 商品模型
 * 对应表: goods
 */

class Goods extends Model
{
    use HasFactory;

    protected $table = 'goods';

    protected static function newFactory()
    {
        return \Database\Factories\GoodsFactory::new();
    }

    protected $fillable = [
        'goods_sn',
        'goods_name',
        'spec',
        'unit',
        'cate_id',
        'scate_id',
        'level',
        'attr',
        'goods_type',
        'goods_channel',
        'discount_rate',
        'slogo',
        'image_list',
        'detail_image_list',
        'remark',
        'brand',
        'place',
        'expire_date',
        'status',
        'schedule_down_time',
        'add_time',
        'update_time',
    ];

    protected $casts = [
        'cate_id' => 'integer',
        'scate_id' => 'integer',
        'level' => 'integer',
        'attr' => 'integer',
        'goods_type' => 'integer',
        'goods_channel' => 'integer',
        'discount_rate' => 'decimal:4',
        'status' => 'integer',
        'schedule_down_time' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 所属一级分类
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'cate_id');
    }

    /**
     * 所属二级分类
     */
    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'scate_id');
    }

    /**
     * 商品状态常量
     */
    const STATUS_OFF = 0;     // 下架
    const STATUS_ON = 1;      // 上架
    const STATUS_AUDIT = 2;   // 待审核

    /**
     * 获取状态文本
     */
    public function getStatusText(): string
    {
        $statusMap = [
            self::STATUS_OFF => '已下架',
            self::STATUS_ON => '已上架',
            self::STATUS_AUDIT => '待审核',
        ];
        return $statusMap[$this->status] ?? '未知';
    }

    /**
     * 搜索作用域
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            $query->where('goods_name', 'like', "%{$keyword}%");
        }
        return $query;
    }

    /**
     * 分类筛选作用域
     */
    public function scopeByCategory($query, $categoryId)
    {
        if ($categoryId) {
            $query->where('cate_id', $categoryId);
        }
        return $query;
    }

    /**
     * 状态筛选作用域
     */
    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        return $query;
    }
}