<?php

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 价格网模型
 * 对应表: goods_jiagewang
 * 用于记录商品价格变更历史
 */
class GoodsJiagewang extends Model
{
    use SoftDeletes;

    protected $table = 'goods_jiagewang';

    protected $fillable = [
        'goods_id',
        'supplier_id',
        'school_id',
        'price',
        'old_price',
        'change_reason',
        'change_type',
        'operator_id',
        'audit_status',
        'audit_time',
        'auditor_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'change_type' => 'integer',
        'audit_status' => 'integer',
        'audit_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联商品
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }

    /**
     * 关联供应商
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier\Supplier::class, 'supplier_id');
    }

    /**
     * 关联学校
     */
    public function school()
    {
        return $this->belongsTo(\App\Models\School\School::class, 'school_id');
    }

    /**
     * 变更类型常量
     */
    const CHANGE_TYPE_UP = 1;       // 价格上调
    const CHANGE_TYPE_DOWN = 2;     // 价格下调
    const CHANGE_TYPE_NEW = 3;      // 新增定价

    /**
     * 审核状态常量
     */
    const AUDIT_STATUS_PENDING = 0; // 待审核
    const AUDIT_STATUS_APPROVED = 1; // 已通过
    const AUDIT_STATUS_REJECTED = 2; // 已拒绝

    /**
     * 获取变更类型文本
     */
    public function getChangeTypeText(): string
    {
        $map = [
            self::CHANGE_TYPE_UP => '价格上调',
            self::CHANGE_TYPE_DOWN => '价格下调',
            self::CHANGE_TYPE_NEW => '新增定价',
        ];
        return $map[$this->change_type] ?? '未知';
    }

    /**
     * 获取审核状态文本
     */
    public function getAuditStatusText(): string
    {
        $map = [
            self::AUDIT_STATUS_PENDING => '待审核',
            self::AUDIT_STATUS_APPROVED => '已通过',
            self::AUDIT_STATUS_REJECTED => '已拒绝',
        ];
        return $map[$this->audit_status] ?? '未知';
    }
}