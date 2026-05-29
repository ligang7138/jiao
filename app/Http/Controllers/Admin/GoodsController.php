<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goods\GoodsCreateRequest;
use App\Http\Requests\Goods\GoodsUpdateRequest;
use App\Services\Goods\GoodsService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 商品管理控制器
 */
class GoodsController extends Controller
{
    protected GoodsService $goodsService;

    public function __construct(GoodsService $goodsService)
    {
        $this->goodsService = $goodsService;
    }

    /**
     * 商品列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $params = $request->only([
            'keyword',
            'name',
            'pid',
            'parent_id',
            'status',
            'page',
            'page_size',
        ]);

        $result = $this->goodsService->getList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 创建商品
     *
     * @param GoodsCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(GoodsCreateRequest $request)
    {
        $data = $request->validated();

        try {
            $goods = $this->goodsService->create($data);

            return ResponseHelper::success([
                'id' => $goods->id,
                'goods_name' => $goods->goods_name,
            ], '新增成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '新增失败:' . $e->getMessage());
        }
    }

    /**
     * 商品详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $detail = $this->goodsService->getDetail($id);

            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error('商品不存在');
        }
    }

    /**
     * 更新商品
     *
     * @param GoodsUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(GoodsUpdateRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $goods = $this->goodsService->update($id, $data);

            return ResponseHelper::success([
                'id' => $goods->id,
            ], '编辑成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '编辑失败' . $e->getMessage());
        }
    }

    /**
     * 删除商品
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->goodsService->delete($id);

            return ResponseHelper::success([], '商品删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('商品删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除商品
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return ResponseHelper::error('请选择要删除的商品');
        }

        try {
            $count = $this->goodsService->batchDelete($ids);

            return ResponseHelper::success([
                'deleted_count' => $count,
            ], '批量删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('批量删除失败: ' . $e->getMessage());
        }
    }

    /**
     * 更改商品状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        if (!in_array($status, [0, 1, 2])) {
            return ResponseHelper::error('状态值无效');
        }

        try {
            $goods = $this->goodsService->changeStatus($id, $status);

            return ResponseHelper::success([
                'id' => $goods->id,
                'status' => $goods->status,
                'status_text' => $goods->getStatusText(),
            ], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('状态更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取供应商商品列表
     *
     * @param int $supplierId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupplierGoods(int $supplierId)
    {
        try {
            $goods = $this->goodsService->getSupplierGoods($supplierId);

            return ResponseHelper::success($goods);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取商品失败: ' . $e->getMessage());
        }
    }

    /**
     * 商品上架
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish(int $id)
    {
        try {
            $goods = $this->goodsService->publish($id);

            return ResponseHelper::success([
                'id' => $goods->id,
                'status' => $goods->status,
            ], '上架成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '上架失败');
        }
    }

    /**
     * 商品下架
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unpublish(Request $request, int $id)
    {
        $downType = (int) $request->input('down_type', 1);
        $reason = trim((string) $request->input('reason', ''));

        try {
            $goods = $this->goodsService->unpublish($id, $downType, $reason);

            $msg = $downType === 1
                ? '下架成功'
                : '预下架设置成功，将于' . date('Y-m-d H:i:s', (int) $goods->schedule_down_time) . '自动下架';

            return ResponseHelper::success([
                'id' => $goods->id,
                'status' => $goods->status,
                'schedule_down_time' => $goods->schedule_down_time,
            ], $msg);
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '操作失败');
        }
    }

    /**
     * 批量上架
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchPublish(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return ResponseHelper::error('请选择要上架的商品');
        }

        try {
            $count = $this->goodsService->batchPublish($ids);

            return ResponseHelper::success([
                'count' => $count,
            ], '批量上架成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('批量上架失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量下架
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchUnpublish(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return ResponseHelper::error('请选择要下架的商品');
        }

        try {
            $count = $this->goodsService->batchUnpublish($ids);

            return ResponseHelper::success([
                'count' => $count,
            ], '批量下架成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('批量下架失败: ' . $e->getMessage());
        }
    }

    /**
     * 上下架记录
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusLog(int $id)
    {
        try {
            $logs = $this->goodsService->getStatusLog($id);

            return ResponseHelper::success($logs);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取记录失败: ' . $e->getMessage());
        }
    }

    /**
     * 历史价格
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function historyPrice(Request $request, int $id)
    {
        $params = $request->only(['start_date', 'end_date']);

        try {
            $prices = $this->goodsService->getHistoryPrice($id, $params);

            return ResponseHelper::success($prices);
        } catch (\Exception $e) {
            return ResponseHelper::error('获取历史价格失败: ' . $e->getMessage());
        }
    }

    /**
     * 商品导入
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $result = $this->goodsService->import($request->file('file'));

            return ResponseHelper::success($result, '商品导入成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('商品导入失败: ' . $e->getMessage());
        }
    }

    /**
     * 商品导出
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $params = $request->only([
            'keyword',
            'cate_id',
            'status',
        ]);

        try {
            $file = $this->goodsService->export($params);

            return response()->download($file, 'goods_export_' . date('YmdHis') . '.xlsx')
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return ResponseHelper::error('商品导出失败: ' . $e->getMessage());
        }
    }

    /**
     * 商品单位列表
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function units()
    {
        $units = $this->goodsService->getUnits();

        return ResponseHelper::success($units);
    }
}