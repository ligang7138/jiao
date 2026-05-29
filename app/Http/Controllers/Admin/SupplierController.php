<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\SupplierCreateRequest;
use App\Http\Requests\Supplier\SupplierUpdateRequest;
use App\Services\Supplier\SupplierService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 供应商管理控制器
 */
class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * 供应商列表
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'status',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->supplierService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 创建供应商
     */
    public function store(SupplierCreateRequest $request)
    {
        $data = $request->validated();

        try {
            $supplier = $this->supplierService->create($data);

            return ResponseHelper::success([
                'id' => $supplier->id,
                'supplier_name' => $supplier->supplier_name,
            ], '供应商创建成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('供应商创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 供应商详情
     */
    public function show(int $id)
    {
        try {
            $detail = $this->supplierService->getDetail($id);

            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error('供应商不存在');
        }
    }

    /**
     * 更新供应商
     */
    public function update(SupplierUpdateRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $supplier = $this->supplierService->update($id, $data);

            return ResponseHelper::success([
                'id' => $supplier->id,
            ], '供应商更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('供应商更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除供应商
     */
    public function destroy(int $id)
    {
        try {
            $this->supplierService->delete($id);

            return ResponseHelper::success([], '供应商删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * 更改供应商状态
     */
    public function changeStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        if (!in_array((int) $status, [0, 1], true)) {
            return ResponseHelper::error('状态值无效，仅支持 0=停用、1=启用');
        }

        try {
            $supplier = $this->supplierService->changeStatus($id, $status);

            return ResponseHelper::success([
                'id' => $supplier->id,
                'status' => $supplier->status,
                'status_text' => $supplier->getStatusText(),
            ], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('状态更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取所有启用的供应商
     */
    public function getActiveSuppliers()
    {
        $suppliers = $this->supplierService->getActiveSuppliers();

        return ResponseHelper::success($suppliers);
    }

    /**
     * 获取折扣变更记录
     */
    public function getDiscountLogs(int $id)
    {
        try {
            $logs = $this->supplierService->getDiscountLogs($id);

            return ResponseHelper::success($logs);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取折扣记录失败: ' . $e->getMessage());
        }
    }
}