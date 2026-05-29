<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Backorder\BackorderService;
use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;
use Illuminate\Http\Request;

/**
 * 退货单管理控制器
 */
class BackorderController extends Controller
{
    protected BackorderService $backorderService;

    public function __construct(BackorderService $backorderService)
    {
        $this->backorderService = $backorderService;
    }

    /**
     * 退货单列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'status',
            'type',
            'school_id',
            'supplier_id',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->backorderService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 退货单详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $detail = $this->backorderService->getDetail($id);
            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::NOT_FOUND, '退货单不存在');
        }
    }

    /**
     * 审核通过
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function audit(Request $request, int $id)
    {
        $solution = $request->input('solution', '');

        try {
            $backorder = $this->backorderService->audit($id, $solution);

            return ResponseHelper::success([
                'id' => $backorder->id,
                'status' => $backorder->status,
                'status_text' => $backorder->getStatusText(),
            ], '审核通过成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::BUSINESS_CONDITION_NOT_MET, $e->getMessage());
        }
    }

    /**
     * 审核拒绝
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function auditReject(Request $request, int $id)
    {
        $reason = $request->input('reason', '');

        if (empty($reason)) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, '拒绝原因不能为空');
        }

        try {
            $backorder = $this->backorderService->auditReject($id, $reason);

            return ResponseHelper::success([
                'id' => $backorder->id,
                'status' => $backorder->status,
                'status_text' => $backorder->getStatusText(),
            ], '审核拒绝成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::BUSINESS_CONDITION_NOT_MET, $e->getMessage());
        }
    }

    /**
     * 取消退货单
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, int $id)
    {
        $reason = $request->input('reason', '');

        try {
            $backorder = $this->backorderService->cancel($id, $reason);

            return ResponseHelper::success([
                'id' => $backorder->id,
                'status' => $backorder->status,
                'status_text' => $backorder->getStatusText(),
            ], '取消成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::BUSINESS_CONDITION_NOT_MET, $e->getMessage());
        }
    }

    /**
     * 设置解决方案
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function solution(Request $request, int $id)
    {
        $solution = $request->input('solution', '');

        if (empty($solution)) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, '解决方案不能为空');
        }

        try {
            $backorder = $this->backorderService->setSolution($id, $solution);

            return ResponseHelper::success([
                'id' => $backorder->id,
                'solution' => $backorder->solution,
            ], '设置成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::BUSINESS_CONDITION_NOT_MET, $e->getMessage());
        }
    }

    /**
     * 退货原因类型列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeIndex(Request $request)
    {
        $params = $request->only([
            'keyword',
            'status',
            'home',
            'page',
            'page_size',
        ]);

        $result = $this->backorderService->getTypeList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 获取启用的退货原因类型（下拉选择用）
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeOptions()
    {
        $types = $this->backorderService->getEnabledTypes();

        return ResponseHelper::success($types);
    }

    /**
     * 新增退货原因类型
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50|unique:backorder_type,name',
            'home' => 'nullable|integer|in:0,1',
            'sort' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $type = $this->backorderService->createType($data);

            return ResponseHelper::success([
                'id' => $type->id,
            ], '新增成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::DATABASE_ERROR, '新增失败: ' . $e->getMessage());
        }
    }

    /**
     * 编辑退货原因类型
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeUpdate(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:50|unique:backorder_type,name,' . $id,
            'home' => 'nullable|integer|in:0,1',
            'sort' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $type = $this->backorderService->updateType($id, $data);

            return ResponseHelper::success([
                'id' => $type->id,
            ], '更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::DATABASE_ERROR, '更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 设置退货原因类型状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        if (!in_array($status, [0, 1])) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, '状态值无效');
        }

        try {
            $type = $this->backorderService->setTypeStatus($id, $status);

            return ResponseHelper::success([
                'id' => $type->id,
                'status' => $type->status,
                'status_text' => $type->getStatusText(),
            ], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::DATABASE_ERROR, '状态更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 设置退货原因类型前台显示
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeHome(Request $request, int $id)
    {
        $home = $request->input('home');

        if (!in_array($home, [0, 1])) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, '显示状态值无效');
        }

        try {
            $type = $this->backorderService->setTypeHome($id, $home);

            return ResponseHelper::success([
                'id' => $type->id,
                'home' => $type->home,
                'home_text' => $type->getHomeText(),
            ], '设置成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::DATABASE_ERROR, '设置失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除退货原因类型
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function typeDestroy(int $id)
    {
        try {
            $this->backorderService->deleteType($id);

            return ResponseHelper::success([], '删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(ErrorCode::BUSINESS_CONDITION_NOT_MET, $e->getMessage());
        }
    }
}
