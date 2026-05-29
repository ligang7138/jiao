<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\StatService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 统计分析控制器
 */
class StatController extends Controller
{
    protected StatService $statService;

    public function __construct(StatService $statService)
    {
        $this->statService = $statService;
    }

    /**
     * 订单统计
     */
    public function order(Request $request)
    {
        $params = $request->only([
            'date_type',
            'start_date',
            'end_date',
            'school_id',
            'canteen_id',
            'canteen_type',
            'supplier_id',
        ]);

        $result = $this->statService->getOrderStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 商品统计
     */
    public function goods(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'cate_id',
            'goods_name',
            'page',
            'page_size',
        ]);

        $result = $this->statService->getGoodsStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 退货统计
     */
    public function backorder(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
        ]);

        $result = $this->statService->getBackorderStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 退货率统计
     */
    public function backorderRate(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
            'dimension', // school, supplier, goods
        ]);

        $result = $this->statService->getBackorderRateStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 准时率统计
     */
    public function ontimeRate(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
            'dimension',
        ]);

        $result = $this->statService->getOntimeRateStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 补货统计
     */
    public function replenish(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
        ]);

        $result = $this->statService->getReplenishStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 补货率统计
     */
    public function replenishRate(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
            'dimension',
        ]);

        $result = $this->statService->getReplenishRateStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 投诉统计
     */
    public function complaint(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
        ]);

        $result = $this->statService->getComplaintStat($params);

        return ResponseHelper::success($result);
    }

    /**
     * 比价统计
     */
    public function bidding(Request $request)
    {
        $params = $request->only([
            'start_date',
            'end_date',
            'school_id',
            'supplier_id',
            'cate_id',
        ]);

        $result = $this->statService->getBiddingStat($params);

        return ResponseHelper::success($result);
    }
}
