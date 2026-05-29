<?php

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;

/**
 * 商品单位模型
 * 对应表: goods_unit
 */
class GoodsUnit extends Model
{
    protected $table = 'goods_unit';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'status',
        'add_user',
        'add_time',
        'update_user',
        'update_time',
    ];

    protected $casts = [
        'status' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_OFF = 0;
    const STATUS_ON = 1;
}
