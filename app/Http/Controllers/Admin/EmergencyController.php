<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Emergency\EmergencyService;
use App\Http\Requests\Admin\Emergency\ProcessRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 应急管理控制器
 */
class EmergencyController extends Controller
{
    protected EmergencyService $emergencyService;

    public function __construct(EmergencyService $emergencyService)
    {
        $this->emergencyService = $emergencyService;
    }

    /**
     * 应急事件列表
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'canteen_name',
            'process_status',
            'type_id',
            'start_date',
            'end_date',
            'page',
            'page_size',
        ]);

        $result = $this->emergencyService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 应急事件详情
     */
    public function show(int $id)
    {
        try {
            $detail = $this->emergencyService->getDetail($id);
            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error(40002, '应急事件不存在');
        }
    }

    /**
     * 处理应急事件
     */
    public function process(ProcessRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $emergency = $this->emergencyService->process($id, $data);

            return ResponseHelper::success([
                'id' => $emergency->id,
                'process_status' => $emergency->process_status,
            ], '处理成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }

    /**
     * 应急类型列表
     */
    public function typeIndex(Request $request)
    {
        $params = $request->only(['name', 'status', 'page', 'page_size']);

        $result = $this->emergencyService->getTypeList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 新增应急类型
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
            $type = $this->emergencyService->createType($data);

            return ResponseHelper::success([
                'id' => $type->id,
            ], '新增成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    /**
     * 编辑应急类型
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
            $type = $this->emergencyService->updateType($id, $data);

            return ResponseHelper::success([
                'id' => $type->id,
            ], '编辑成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    /**
     * 设置应急类型状态
     */
    public function typeStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        try {
            $type = $this->emergencyService->updateType($id, ['status' => $status]);

            return ResponseHelper::success([], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    /**
     * 设置前台显示
     */
    public function typeHome(Request $request, int $id)
    {
        $home = $request->input('home');

        try {
            $type = $this->emergencyService->updateType($id, ['home' => $home]);

            return ResponseHelper::success([], '设置成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }
}
