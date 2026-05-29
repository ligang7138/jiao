<?php

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品上下架日志
 * 对应表: goods_status_log
 */
class GoodsStatusLog extends Model
{
    protected $table = 'goods_status_log';

    public $timestamps = false;

    protected $fillable = [
        'goods_id',
        'goods_sn',
        'goods_name',
        'operator',
        'operate_time',
        'operate_type',
        'old_status',
        'new_status',
        'reason',
        'schedule_down_time',
    ];

    protected $casts = [
        'goods_id' => 'integer',
        'operate_time' => 'integer',
        'operate_type' => 'integer',
        'old_status' => 'integer',
        'new_status' => 'integer',
        'schedule_down_time' => 'integer',
    ];
}
