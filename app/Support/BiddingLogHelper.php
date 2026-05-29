<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * bidding_log 表结构兼容（旧库无 school_id，经 school_canteen 关联学校）
 */
class BiddingLogHelper
{
    public static function hasSchoolIdColumn(): bool
    {
        return Schema::hasTable('bidding_log')
            && Schema::hasColumn('bidding_log', 'school_id');
    }

    /**
     * @param  int[]  $schoolIds
     * @return array<int, int>
     */
    public static function countSuppliersBySchoolIds(array $schoolIds): array
    {
        if ($schoolIds === [] || !Schema::hasTable('bidding_log')) {
            return [];
        }

        if (self::hasSchoolIdColumn()) {
            return DB::table('bidding_log')
                ->select('school_id', DB::raw('count(1) as num'))
                ->where('status', 1)
                ->whereIn('school_id', $schoolIds)
                ->groupBy('school_id')
                ->pluck('num', 'school_id')
                ->map(fn ($v) => (int) $v)
                ->all();
        }

        if (!Schema::hasTable('school_canteen')) {
            return [];
        }

        return DB::table('bidding_log as bl')
            ->join('school_canteen as sc', 'sc.id', '=', 'bl.canteen_id')
            ->select('sc.school_id', DB::raw('count(1) as num'))
            ->where('bl.status', 1)
            ->whereIn('sc.school_id', $schoolIds)
            ->groupBy('sc.school_id')
            ->pluck('num', 'school_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    public static function applySchoolExists(Builder $query, string $schoolAlias = 's'): void
    {
        if (self::hasSchoolIdColumn()) {
            $query->whereExists(function ($sub) use ($schoolAlias) {
                $sub->select(DB::raw(1))
                    ->from('bidding_log as bl')
                    ->whereColumn('bl.school_id', "{$schoolAlias}.id");
            });

            return;
        }

        $query->whereExists(function ($sub) use ($schoolAlias) {
            $sub->select(DB::raw(1))
                ->from('bidding_log as bl')
                ->join('school_canteen as sc', 'sc.id', '=', 'bl.canteen_id')
                ->whereColumn('sc.school_id', "{$schoolAlias}.id");
        });
    }

    public static function applySchoolNotExists(Builder $query, string $schoolAlias = 's'): void
    {
        if (self::hasSchoolIdColumn()) {
            $query->whereNotExists(function ($sub) use ($schoolAlias) {
                $sub->select(DB::raw(1))
                    ->from('bidding_log as bl')
                    ->whereColumn('bl.school_id', "{$schoolAlias}.id");
            });

            return;
        }

        $query->whereNotExists(function ($sub) use ($schoolAlias) {
            $sub->select(DB::raw(1))
                ->from('bidding_log as bl')
                ->join('school_canteen as sc', 'sc.id', '=', 'bl.canteen_id')
                ->whereColumn('sc.school_id', "{$schoolAlias}.id");
        });
    }
}
