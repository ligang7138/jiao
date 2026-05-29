<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Complaint\ComplaintService;
use App\Http\Requests\Admin\Complaint\ProcessRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 投诉管理控制器
 */
class ComplaintController extends Controller
{
    protected ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    /**
     * 投诉列表
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'canteen_name',
            'process_status',
            'review_status',
            'type_id',
            'start_date',
            'end_date',
            'page',
            'page_size',
        ]);

        $result = $this->complaintService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 投诉详情
     */
    public function show(int $id)
    {
        try {
            $detail = $this->complaintService->getDetail($id);
            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error(40002, '投诉不存在');
        }
    }

    /**
     * 处理投诉
     */
    public function process(ProcessRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $complaint = $this->complaintService->process($id, $data);

            return ResponseHelper::success([
                'id' => $complaint->id,
                'process_status' => $complaint->process_status,
            ], '处理成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }

    /**
     * 投诉类型列表
     */
    public function typeIndex(Request $request)
    {
        $params = $request->only(['name', 'status', 'page', 'page_size']);

        $result = $this->complaintService->getTypeList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 新增投诉类型
     */
    public function typeStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'home' => 'nullable|integer|in:0,1',
            'sort' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $type = $this->complaintService->createType($data);

            return ResponseHelper::success([
                'id' => $type->id,
            ], '新增成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    /**
     * 编辑投诉类型
     */
    public function typeUpdate(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:50',
            'home' => 'nullable|integer|in:0,1',
            'sort' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $type = $this->complaintService->updateType($id, $data);

            return ResponseHelper::success([
                'id' => $type->id,
            ], '编辑成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    /**
     * 设置投诉类型状态
     */
    public function typeStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        try {
            $this->complaintService->updateType($id, ['status' => $status]);

            return ResponseHelper::success([], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }
}
