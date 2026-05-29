<?php

namespace App\Models\Supplier;

use App\Models\Goods\Goods;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 供应商报价日志
 * 对应表: discount_log
 */
class DiscountLog extends Model
{
    public $timestamps = false;

    protected $table = 'discount_log';

    protected $fillable = [
        'goods_id',
        'supp_id',
        'quotation_price',
        'limit_price',
        'float_rate',
        'add_time',
    ];

    protected $casts = [
        'goods_id' => 'integer',
        'supp_id' => 'integer',
        'quotation_price' => 'decimal:2',
        'limit_price' => 'decimal:2',
        'float_rate' => 'decimal:4',
        'add_time' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supp_id');
    }

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }
}
