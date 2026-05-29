<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 订单商品模型
 * 对应表: orders_goods
 */
class OrderGoods extends Model
{
    use SoftDeletes;

    protected $table = 'orders_goods';

    protected $fillable = [
        'order_id',
        'goods_id',
        'goods_name',
        'unit',
        'spec',
        'price',
        'quantity',
        'amount',
        'remark',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 所属订单
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * 关联商品
     */
    public function goods()
    {
        return $this->belongsTo(\App\Models\Goods\Goods::class, 'goods_id');
    }
}