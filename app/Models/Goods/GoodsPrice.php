<?php

namespace App\Models\Goods;

use App\Models\Supplier\Supplier;
use Illuminate\Database\Eloquent\Model;

/**
 * 供应商商品报价
 * 对应表: goods_price
 */
class GoodsPrice extends Model
{
    public $timestamps = false;

    protected $table = 'goods_price';

    protected $fillable = [
        'bind_sn',
        'supp_id',
        'goods_id',
        'sale_price',
        'tax_rate',
        'class_code',
    ];

    protected $casts = [
        'supp_id' => 'integer',
        'goods_id' => 'integer',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supp_id');
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }
}
