<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Services\Goods\GoodsUnitService;
use Illuminate\Http\Request;

/**
 * 商品单位管理控制器
 */
class GoodsUnitController extends Controller
{
    public function __construct(
        protected GoodsUnitService $unitService
    ) {}

    public function index(Request $request)
    {
        $params = $request->only(['name', 'status', 'page', 'page_size']);
        $result = $this->unitService->getList($params);

        return ResponseHelper::success($result);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20',
        ], [
            'name.required' => '单位名称不能为空',
        ]);

        try {
            $unit = $this->unitService->create($request->only('name'));

            return ResponseHelper::success(['id' => $unit->id], '新增成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '新增失败');
        }
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:20',
        ], [
            'name.required' => '单位名称不能为空',
        ]);

        try {
            $unit = $this->unitService->update($id, $request->only('name'));

            return ResponseHelper::success(['id' => $unit->id], '编辑成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '编辑失败');
        }
    }

    public function setStatus(Request $request, int $id)
    {
        $status = (int) $request->input('status', 0);

        try {
            $unit = $this->unitService->changeStatus($id, $status);

            return ResponseHelper::success([
                'id' => $unit->id,
                'status' => $unit->status,
            ], '设置成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40003, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '设置失败');
        }
    }
}
