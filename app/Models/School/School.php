<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 学校模型
 * 对应表: school
 */
class School extends Model
{
    use HasFactory;

    protected $table = 'school';

    public $timestamps = false;

    protected static function newFactory()
    {
        return \Database\Factories\SchoolFactory::new();
    }

    protected $fillable = [
        'school_sn',
        'school_name',
        'school_district',
        'school_subdistrict',
        'school_period',
        'bank_no',
        'taxpayer_no',
        'invoice_title',
        'invoice_phone',
        'invoice_address',
        'account_create_type',
        'account_type',
        'account_period',
        'account_time',
        'account_start_date',
        'account_end_date',
        'account_execute_date',
        'status',
        'add_time',
        'update_time',
    ];

    protected $casts = [
        'account_type' => 'integer',
        'account_period' => 'integer',
        'account_time' => 'integer',
        'account_create_type' => 'integer',
        'status' => 'integer',
        'add_time' => 'integer',
        'update_time' => 'integer',
    ];

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    public function getStatusText(): string
    {
        return match ((int) $this->status) {
            self::STATUS_ENABLED => '启用',
            self::STATUS_DISABLED => '停用',
            default => '未知',
        };
    }

    public function canteens()
    {
        return $this->hasMany(Canteen::class, 'school_id');
    }

    public function users()
    {
        return $this->hasMany(SchoolUser::class, 'school_id');
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('school_name', 'like', "%{$keyword}%")
                    ->orWhere('school_sn', 'like', "%{$keyword}%")
                    ->orWhere('school_district', 'like', "%{$keyword}%");
            });
        }

        return $query;
    }

    public function scopeByStatus($query, $status)
    {
        if ($status !== null && $status !== '') {
            $query->where('status', (int) $status);
        }

        return $query;
    }
}
