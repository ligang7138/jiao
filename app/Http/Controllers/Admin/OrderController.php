<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 订单管理控制器
 */
class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * 订单列表
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'date_type',
            'start_date',
            'end_date',
            'order_sn',
            'canteen_name',
            'canteen_type',
            'order_type',
            'status',
            'supp_id',
            'supplier_id',
            'school_id',
            'audit_status',
            'is_send_late',
            'inspection_report_status',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->orderService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 创建订单
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'school_id' => 'required|integer|exists:school,id',
            'canteen_id' => 'required|integer|exists:school_canteen,id',
            'supplier_id' => 'required|integer|exists:supplier,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'remark' => 'nullable|string|max:500',
            'goods' => 'required|array',
            'goods.*.goods_id' => 'nullable|integer',
            'goods.*.goods_name' => 'required|string|max:255',
            'goods.*.unit' => 'required|string|max:50',
            'goods.*.spec' => 'nullable|string|max:255',
            'goods.*.price' => 'required|numeric|min:0',
            'goods.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $goods = $data['goods'];
            unset($data['goods']);

            $order = $this->orderService->create($data, $goods);

            return ResponseHelper::success([
                'id' => $order->id,
                'order_no' => $order->order_no,
            ], '订单创建成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('订单创建失败: ' . $e->getMessage());
        }
    }

    /**
     * 订单详情
     */
    public function show(int $id)
    {
        try {
            $detail = $this->orderService->getDetail($id);

            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error('订单不存在');
        }
    }

    /**
     * 更新订单
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'school_id' => 'sometimes|required|integer|exists:school,id',
            'canteen_id' => 'sometimes|required|integer|exists:school_canteen,id',
            'supplier_id' => 'sometimes|required|integer|exists:supplier,id',
            'order_date' => 'sometimes|required|date',
            'delivery_date' => 'nullable|date',
            'remark' => 'nullable|string|max:500',
            'goods' => 'sometimes|required|array',
            'goods.*.goods_id' => 'nullable|integer',
            'goods.*.goods_name' => 'required|string|max:255',
            'goods.*.unit' => 'required|string|max:50',
            'goods.*.spec' => 'nullable|string|max:255',
            'goods.*.price' => 'required|numeric|min:0',
            'goods.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $goods = $data['goods'] ?? [];
            unset($data['goods']);

            $order = $this->orderService->update($id, $data, $goods);

            return ResponseHelper::success([
                'id' => $order->id,
            ], '订单更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('订单更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 删除订单
     */
    public function destroy(int $id)
    {
        try {
            $this->orderService->delete($id);

            return ResponseHelper::success([], '订单删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('订单删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 更改订单状态
     */
    public function changeStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        if (!in_array($status, [0, 1, 2, 3, 4, 5])) {
            return ResponseHelper::error('状态值无效');
        }

        try {
            $order = $this->orderService->changeStatus($id, $status);

            return ResponseHelper::success([
                'id' => $order->id,
                'status' => $order->status,
                'status_text' => $order->getStatusText(),
            ], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('状态更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 提交审核
     */
    public function submit(int $id)
    {
        try {
            $order = $this->orderService->changeStatus($id, Order::STATUS_PENDING);

            return ResponseHelper::success([], '订单已提交审核');
        } catch (\Exception $e) {
            return ResponseHelper::error('提交失败: ' . $e->getMessage());
        }
    }

    /**
     * 取消订单
     */
    public function cancel(int $id)
    {
        try {
            $order = $this->orderService->changeStatus($id, Order::STATUS_CANCELLED);

            return ResponseHelper::success([], '订单已取消');
        } catch (\Exception $e) {
            return ResponseHelper::error('取消失败: ' . $e->getMessage());
        }
    }

    /**
     * 订单导出
     */
    public function export(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_id',
            'supplier_id',
            'status',
            'start_date',
            'end_date',
        ]);

        try {
            $file = $this->orderService->export($params);

            return response()->download($file, 'orders_export_' . date('YmdHis') . '.xlsx')
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return ResponseHelper::error('订单导出失败: ' . $e->getMessage());
        }
    }

    /**
     * 订单明细导出
     */
    public function exportDetail(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_id',
            'supplier_id',
            'status',
            'start_date',
            'end_date',
        ]);

        try {
            $file = $this->orderService->exportDetail($params);

            return response()->download($file, 'orders_detail_export_' . date('YmdHis') . '.xlsx')
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return ResponseHelper::error('订单明细导出失败: ' . $e->getMessage());
        }
    }

    /**
     * 订单统计
     */
    public function statistics(Request $request)
    {
        $params = $request->only([
            'keyword',
            'school_id',
            'supplier_id',
            'status',
            'start_date',
            'end_date',
        ]);

        try {
            $stats = $this->orderService->getStatistics($params);

            return ResponseHelper::success($stats);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取统计失败: ' . $e->getMessage());
        }
    }

    /**
     * 溯源信息
     */
    public function traceSource(int $id)
    {
        try {
            $trace = $this->orderService->getTraceSource($id);

            return ResponseHelper::success($trace);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取溯源信息失败: ' . $e->getMessage());
        }
    }
}