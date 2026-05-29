<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Bidding\BiddingService;
use App\Http\Requests\Admin\Bidding\AuditRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 招投标管理控制器
 */
class BiddingController extends Controller
{
    protected BiddingService $biddingService;

    public function __construct(BiddingService $biddingService)
    {
        $this->biddingService = $biddingService;
    }

    /**
     * 合作申请列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_id',
            'canteen_id',
            'supplier_id',
            'audit_status',
            'type',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->biddingService->getHistoryList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 申请详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $detail = $this->biddingService->getHistoryDetail($id);

            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error(40002, '申请记录不存在');
        }
    }

    /**
     * 审核合作申请
     *
     * @param AuditRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(AuditRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $history = $this->biddingService->audit(
                $id,
                $data['audit_status'],
                $data['audit_remark'] ?? ''
            );

            return ResponseHelper::success([
                'id' => $history->id,
                'audit_status' => $history->audit_status,
                'audit_status_text' => $history->getAuditStatusText(),
            ], '审核成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(60005, $e->getMessage());
        }
    }

    /**
     * 合作关系列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logList(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_id',
            'canteen_id',
            'supplier_id',
            'status',
            'effective_status',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->biddingService->getLogList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 供应商报价列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function discount(Request $request)
    {
        $params = $request->only([
            'keyword',
            'goods_id',
            'supplier_id',
            'school_id',
            'canteen_id',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->biddingService->getDiscountList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 报价历史记录
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function discountHistory(Request $request)
    {
        $params = $request->only([
            'goods_id',
            'supplier_id',
            'school_id',
            'canteen_id',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->biddingService->getDiscountHistory($params);

        return ResponseHelper::success($result);
    }

    /**
     * 终止合作关系
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function terminate(Request $request, int $id)
    {
        $remark = $request->input('remark', '');

        try {
            $log = $this->biddingService->terminate($id, $remark);

            return ResponseHelper::success([
                'id' => $log->id,
                'status' => $log->status,
                'status_text' => $log->getStatusText(),
            ], '合作关系已终止');
        } catch (\Exception $e) {
            return ResponseHelper::error(60005, $e->getMessage());
        }
    }
}
