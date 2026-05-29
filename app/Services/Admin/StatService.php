<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

/**
 * 统计分析服务层
 */
class StatService
{
    /**
     * 订单统计
     */
    public function getOrderStat(array $params): array
    {
        $dateType = $params['date_type'] ?? 1; // 1=送货日期, 2=下单时间

        $query = DB::table('orders as o')
            ->join('school_canteen as c', 'o.canteen_id', '=', 'c.id')
            ->join('school as s', 'c.school_id', '=', 's.id')
            ->join('supplier as sp', 'o.supp_id', '=', 'sp.id')
            ->when($params['school_id'] ?? null, function ($q, $id) {
                $q->where('s.id', $id);
            })
            ->when($params['canteen_id'] ?? null, function ($q, $id) {
                $q->where('c.id', $id);
            })
            ->when($params['canteen_type'] ?? null, function ($q, $type) {
                $q->where('c.canteen_type', $type);
            })
            ->when($params['supplier_id'] ?? null, function ($q, $id) {
                $q->where('sp.id', $id);
            });

        if ($dateType == 1) {
            $query->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '>=', $date);
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '<=', $date);
            });
        } else {
            $query->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.add_time', '>=', strtotime($date));
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.add_time', '<=', strtotime($date . ' 23:59:59'));
            });
        }

        // 汇总统计
        $summary = $query->selectRaw("
            COUNT(*) as order_count,
            SUM(o.total_price) as total_amount,
            SUM(o.send_price) as send_amount,
            SUM(o.receive_price) as receive_amount,
            SUM(o.back_price) as back_amount
        ")->first();

        // 按食堂分组统计
        $byCanteen = DB::table('orders as o')
            ->join('school_canteen as c', 'o.canteen_id', '=', 'c.id')
            ->join('school as s', 'c.school_id', '=', 's.id')
            ->groupBy('c.id', 'c.name', 's.school_name', 'c.canteen_type')
            ->selectRaw("
                c.id as canteen_id,
                c.name as canteen_name,
                s.school_name,
                c.canteen_type,
                COUNT(*) as order_count,
                SUM(o.total_price) as total_amount,
                SUM(o.receive_price) as receive_amount
            ")
            ->orderBy('total_amount', 'desc')
            ->limit(20)
            ->get();

        return [
            'summary' => $summary,
            'by_canteen' => $byCanteen,
        ];
    }

    /**
     * 商品统计
     */
    public function getGoodsStat(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = DB::table('orders_goods as og')
            ->join('orders as o', 'og.order_id', '=', 'o.id')
            ->join('goods as g', 'og.goods_id', '=', 'g.id')
            ->when($params['cate_id'] ?? null, function ($q, $id) {
                $q->where('g.cate_id', $id);
            })
            ->when($params['goods_name'] ?? null, function ($q, $name) {
                $q->where('g.goods_name', 'like', "%{$name}%");
            })
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '>=', $date);
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '<=', $date);
            })
            ->groupBy('g.id', 'g.goods_sn', 'g.goods_name', 'g.spec', 'g.unit')
            ->selectRaw("
                g.id as goods_id,
                g.goods_sn,
                g.goods_name,
                g.spec,
                g.unit,
                SUM(og.needqty) as need_qty,
                SUM(og.receiveqty) as receive_qty,
                SUM(og.needqty * og.sale_price) as total_amount,
                COUNT(DISTINCT o.id) as order_count
            ")
            ->orderBy('total_amount', 'desc');

        $total = $query->get()->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 退货统计
     */
    public function getBackorderStat(array $params): array
    {
        $query = DB::table('backorder as b')
            ->join('orders as o', 'b.order_id', '=', 'o.id')
            ->join('orders_goods as og', 'b.order_goods_id', '=', 'og.id')
            ->when($params['school_id'] ?? null, function ($q, $id) {
                $q->where('o.school_id', $id);
            })
            ->when($params['supplier_id'] ?? null, function ($q, $id) {
                $q->where('o.supp_id', $id);
            })
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('b.add_time', '>=', strtotime($date));
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('b.add_time', '<=', strtotime($date . ' 23:59:59'));
            });

        $summary = $query->selectRaw("
            COUNT(*) as back_count,
            SUM(b.quantity) as back_qty,
            SUM(b.quantity * og.sale_price) as back_amount,
            SUM(CASE WHEN b.status = 4 THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN b.status = 4 THEN b.quantity ELSE 0 END) as approved_qty
        ")->first();

        return [
            'summary' => $summary,
        ];
    }

    /**
     * 退货率统计
     */
    public function getBackorderRateStat(array $params): array
    {
        $dimension = $params['dimension'] ?? 'school';

        $query = DB::table('orders_goods as og')
            ->join('orders as o', 'og.order_id', '=', 'o.id')
            ->join('school_canteen as c', 'o.canteen_id', '=', 'c.id')
            ->join('school as s', 'c.school_id', '=', 's.id')
            ->join('supplier as sp', 'o.supp_id', '=', 'sp.id')
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '>=', $date);
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '<=', $date);
            });

        if ($dimension == 'school') {
            $query->groupBy('s.id', 's.school_name');
            $select = "s.id as school_id, s.school_name";
        } elseif ($dimension == 'supplier') {
            $query->groupBy('sp.id', 'sp.name');
            $select = "sp.id as supplier_id, sp.name as supplier_name";
        } else {
            $query->groupBy('og.goods_id', 'g.goods_name');
            $query->join('goods as g', 'og.goods_id', '=', 'g.id');
            $select = "og.goods_id, g.goods_name";
        }

        $list = $query->selectRaw("
            {$select},
            SUM(og.receiveqty) as receive_qty,
            SUM(og.backqty) as back_qty,
            ROUND(SUM(og.backqty) / SUM(og.receiveqty) * 100, 2) as back_rate
        ")
            ->orderBy('back_rate', 'desc')
            ->limit(20)
            ->get();

        return [
            'list' => $list,
        ];
    }

    /**
     * 准时率统计
     */
    public function getOntimeRateStat(array $params): array
    {
        $dimension = $params['dimension'] ?? 'supplier';

        $query = DB::table('orders as o')
            ->join('school_canteen as c', 'o.canteen_id', '=', 'c.id')
            ->join('supplier as sp', 'o.supp_id', '=', 'sp.id')
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '>=', $date);
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '<=', $date);
            });

        if ($dimension == 'school') {
            $query->groupBy('c.school_id');
            $query->join('school as s', 'c.school_id', '=', 's.id');
            $select = "s.id as school_id, s.school_name";
        } else {
            $query->groupBy('sp.id', 'sp.name');
            $select = "sp.id as supplier_id, sp.name as supplier_name";
        }

        $list = $query->selectRaw("
            {$select},
            COUNT(*) as delivery_count,
            SUM(CASE WHEN o.is_send_late = 0 THEN 1 ELSE 0 END) as ontime_count,
            SUM(CASE WHEN o.is_send_late = 1 THEN 1 ELSE 0 END) as late_count,
            ROUND(SUM(CASE WHEN o.is_send_late = 0 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as ontime_rate
        ")
            ->orderBy('ontime_rate', 'asc')
            ->limit(20)
            ->get();

        return [
            'list' => $list,
        ];
    }

    /**
     * 补货统计
     */
    public function getReplenishStat(array $params): array
    {
        $query = DB::table('orders as o')
            ->when($params['school_id'] ?? null, function ($q, $id) {
                $q->where('o.school_id', $id);
            })
            ->when($params['supplier_id'] ?? null, function ($q, $id) {
                $q->where('o.supp_id', $id);
            })
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '>=', $date);
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '<=', $date);
            });

        $summary = $query->selectRaw("
            COUNT(*) as total_count,
            SUM(CASE WHEN o.order_type = 2 THEN 1 ELSE 0 END) as replenish_count,
            SUM(CASE WHEN o.order_type = 2 THEN o.total_price ELSE 0 END) as replenish_amount
        ")->first();

        return [
            'summary' => $summary,
        ];
    }

    /**
     * 补货率统计
     */
    public function getReplenishRateStat(array $params): array
    {
        $dimension = $params['dimension'] ?? 'school';

        $query = DB::table('orders as o')
            ->join('school_canteen as c', 'o.canteen_id', '=', 'c.id')
            ->join('supplier as sp', 'o.supp_id', '=', 'sp.id')
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '>=', $date);
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('o.send_date', '<=', $date);
            });

        if ($dimension == 'school') {
            $query->groupBy('c.school_id');
            $query->join('school as s', 'c.school_id', '=', 's.id');
            $select = "s.id as school_id, s.school_name";
        } else {
            $query->groupBy('sp.id', 'sp.name');
            $select = "sp.id as supplier_id, sp.name as supplier_name";
        }

        $list = $query->selectRaw("
            {$select},
            COUNT(*) as total_count,
            SUM(CASE WHEN o.order_type = 2 THEN 1 ELSE 0 END) as replenish_count,
            ROUND(SUM(CASE WHEN o.order_type = 2 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as replenish_rate
        ")
            ->orderBy('replenish_rate', 'desc')
            ->limit(20)
            ->get();

        return [
            'list' => $list,
        ];
    }

    /**
     * 投诉统计
     */
    public function getComplaintStat(array $params): array
    {
        $query = DB::table('complaint as c')
            ->join('orders as o', 'c.order_id', '=', 'o.id')
            ->when($params['school_id'] ?? null, function ($q, $id) {
                $q->where('o.school_id', $id);
            })
            ->when($params['supplier_id'] ?? null, function ($q, $id) {
                $q->where('o.supp_id', $id);
            })
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('c.add_time', '>=', strtotime($date));
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('c.add_time', '<=', strtotime($date . ' 23:59:59'));
            });

        $summary = $query->selectRaw("
            COUNT(*) as complaint_count,
            SUM(CASE WHEN c.process_status = 1 THEN 1 ELSE 0 END) as processed_count,
            SUM(CASE WHEN c.process_status = 0 THEN 1 ELSE 0 END) as pending_count
        ")->first();

        return [
            'summary' => $summary,
        ];
    }

    /**
     * 比价统计
     */
    public function getBiddingStat(array $params): array
    {
        $query = DB::table('discount_log as d')
            ->join('goods as g', 'd.goods_id', '=', 'g.id')
            ->join('supplier as sp', 'd.supp_id', '=', 'sp.id')
            ->join('school_canteen as c', 'd.canteen_id', '=', 'c.id')
            ->join('school as s', 'c.school_id', '=', 's.id')
            ->when($params['school_id'] ?? null, function ($q, $id) {
                $q->where('s.id', $id);
            })
            ->when($params['supplier_id'] ?? null, function ($q, $id) {
                $q->where('sp.id', $id);
            })
            ->when($params['cate_id'] ?? null, function ($q, $id) {
                $q->where('g.cate_id', $id);
            });

        // 按分类统计平均折扣率
        $byCategory = $query->groupBy('g.cate_id')
            ->join('category as cat', 'g.cate_id', '=', 'cat.id')
            ->selectRaw("
                cat.id as cate_id,
                cat.name as cate_name,
                AVG(d.discount) as avg_discount,
                COUNT(*) as count
            ")
            ->orderBy('avg_discount', 'desc')
            ->get();

        return [
            'by_category' => $byCategory,
        ];
    }
}