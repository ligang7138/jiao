<?php

namespace App\Services\Backorder;

use App\Models\Backorder\Backorder;
use App\Models\Backorder\BackorderType;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 退货单服务层
 */
class BackorderService
{
    /**
     * 获取退货单列表
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = Backorder::with(['order.school', 'order.canteen', 'order.supplier', 'orderGoods', 'reasonType'])
            ->search($params['keyword'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->byType($params['type'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

        // 学校筛选
        if (!empty($params['school_id'])) {
            $query->whereHas('order', function ($q) use ($params) {
                $q->where('school_id', $params['school_id']);
            });
        }

        // 供应商筛选
        if (!empty($params['supplier_id'])) {
            $query->whereHas('order', function ($q) use ($params) {
                $q->where('supplier_id', $params['supplier_id']);
            });
        }

        $sortField = $params['sort_field'] ?? 'id';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'order_no' => $item->order?->order_no,
                    'school_name' => $item->order?->school?->school_name,
                    'canteen_name' => $item->order?->canteen?->canteen_name,
                    'supplier_name' => $item->order?->supplier?->supplier_name,
                    'goods_name' => $item->orderGoods?->goods_name,
                    'quantity' => $item->quantity,
                    'type' => $item->type,
                    'type_text' => $item->getTypeText(),
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'reason' => $item->reason,
                    'reason_type_name' => $item->reasonType?->name,
                    'solution' => $item->solution,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取退货单详情
     */
    public function getDetail(int $id): array
    {
        $backorder = Backorder::with([
            'order.school',
            'order.canteen',
            'order.supplier',
            'orderGoods',
            'reasonType',
            'auditor',
            'canceller'
        ])->findOrFail($id);

        return [
            'id' => $backorder->id,
            'order_id' => $backorder->order_id,
            'order_no' => $backorder->order?->order_no,
            'order_info' => $backorder->order ? [
                'id' => $backorder->order->id,
                'order_no' => $backorder->order->order_no,
                'school_name' => $backorder->order->school?->school_name,
                'canteen_name' => $backorder->order->canteen?->canteen_name,
                'supplier_name' => $backorder->order->supplier?->supplier_name,
                'order_date' => $backorder->order->order_date?->format('Y-m-d'),
                'total_amount' => $backorder->order->total_amount,
            ] : null,
            'order_goods_id' => $backorder->order_goods_id,
            'goods_info' => $backorder->orderGoods ? [
                'id' => $backorder->orderGoods->id,
                'goods_name' => $backorder->orderGoods->goods_name,
                'unit' => $backorder->orderGoods->unit,
                'spec' => $backorder->orderGoods->spec,
                'price' => $backorder->orderGoods->price,
                'quantity' => $backorder->orderGoods->quantity,
                'amount' => $backorder->orderGoods->amount,
            ] : null,
            'quantity' => $backorder->quantity,
            'type' => $backorder->type,
            'type_text' => $backorder->getTypeText(),
            'status' => $backorder->status,
            'status_text' => $backorder->getStatusText(),
            'reason' => $backorder->reason,
            'reason_type_id' => $backorder->reason_type_id,
            'reason_type_name' => $backorder->reasonType?->name,
            'solution' => $backorder->solution,
            'remark' => $backorder->remark,
            'audit_user_id' => $backorder->audit_user_id,
            'audit_user_name' => $backorder->auditor?->name,
            'audit_time' => $backorder->audit_time?->format('Y-m-d H:i:s'),
            'cancel_user_id' => $backorder->cancel_user_id,
            'cancel_user_name' => $backorder->canceller?->name,
            'cancel_time' => $backorder->cancel_time?->format('Y-m-d H:i:s'),
            'cancel_reason' => $backorder->cancel_reason,
            'created_at' => $backorder->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $backorder->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 审核通过
     */
    public function audit(int $id, string $solution = ''): Backorder
    {
        DB::beginTransaction();
        try {
            $backorder = Backorder::findOrFail($id);

            if (!$backorder->canAudit()) {
                throw new \Exception('当前状态不允许审核');
            }

            $backorder->update([
                'status' => Backorder::STATUS_APPROVED,
                'solution' => $solution,
                'audit_user_id' => Auth::id(),
                'audit_time' => now(),
            ]);

            // 更新订单商品的退货数量
            if ($backorder->order_goods_id) {
                $orderGoods = OrderGoods::find($backorder->order_goods_id);
                if ($orderGoods) {
                    $orderGoods->backqty = ($orderGoods->backqty ?? 0) + $backorder->quantity;
                    $orderGoods->save();
                }
            }

            DB::commit();
            return $backorder->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 审核拒绝
     */
    public function auditReject(int $id, string $reason = ''): Backorder
    {
        DB::beginTransaction();
        try {
            $backorder = Backorder::findOrFail($id);

            if (!$backorder->canAudit()) {
                throw new \Exception('当前状态不允许审核');
            }

            $backorder->update([
                'status' => Backorder::STATUS_REJECTED,
                'remark' => $reason,
                'audit_user_id' => Auth::id(),
                'audit_time' => now(),
            ]);

            DB::commit();
            return $backorder->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 取消退货单
     */
    public function cancel(int $id, string $reason = ''): Backorder
    {
        DB::beginTransaction();
        try {
            $backorder = Backorder::findOrFail($id);

            if (!$backorder->canCancel()) {
                throw new \Exception('当前状态不允许取消');
            }

            $backorder->update([
                'status' => Backorder::STATUS_CANCELLED,
                'cancel_user_id' => Auth::id(),
                'cancel_time' => now(),
                'cancel_reason' => $reason,
            ]);

            DB::commit();
            return $backorder->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 设置解决方案
     */
    public function setSolution(int $id, string $solution): Backorder
    {
        $backorder = Backorder::findOrFail($id);

        // 只有审核通过的退货单才能设置解决方案
        if ($backorder->status !== Backorder::STATUS_APPROVED) {
            throw new \Exception('只有审核通过的退货单才能设置解决方案');
        }

        $backorder->update([
            'solution' => $solution,
        ]);

        return $backorder->fresh();
    }

    /**
     * 获取退货原因类型列表
     */
    public function getTypeList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = BackorderType::query();

        // 名称搜索
        if (!empty($params['keyword'])) {
            $query->where('name', 'like', "%{$params['keyword']}%");
        }

        // 状态筛选
        if (isset($params['status']) && $params['status'] !== '') {
            $query->where('status', $params['status']);
        }

        // 前台显示筛选
        if (isset($params['home']) && $params['home'] !== '') {
            $query->where('home', $params['home']);
        }

        $query->ordered();

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'home' => $item->home,
                    'home_text' => $item->getHomeText(),
                    'sort' => $item->sort,
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取启用的退货原因类型（用于下拉选择）
     */
    public function getEnabledTypes(): array
    {
        return BackorderType::enabled()
            ->ordered()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                ];
            })->toArray();
    }

    /**
     * 创建退货原因类型
     */
    public function createType(array $data): BackorderType
    {
        return BackorderType::create([
            'name' => $data['name'],
            'home' => $data['home'] ?? BackorderType::HOME_NO,
            'sort' => $data['sort'] ?? 0,
            'status' => $data['status'] ?? BackorderType::STATUS_ENABLED,
        ]);
    }

    /**
     * 更新退货原因类型
     */
    public function updateType(int $id, array $data): BackorderType
    {
        $type = BackorderType::findOrFail($id);

        $type->update([
            'name' => $data['name'] ?? $type->name,
            'home' => $data['home'] ?? $type->home,
            'sort' => $data['sort'] ?? $type->sort,
            'status' => $data['status'] ?? $type->status,
        ]);

        return $type->fresh();
    }

    /**
     * 设置退货原因类型状态
     */
    public function setTypeStatus(int $id, int $status): BackorderType
    {
        $type = BackorderType::findOrFail($id);
        $type->update(['status' => $status]);
        return $type->fresh();
    }

    /**
     * 设置退货原因类型前台显示
     */
    public function setTypeHome(int $id, int $home): BackorderType
    {
        $type = BackorderType::findOrFail($id);
        $type->update(['home' => $home]);
        return $type->fresh();
    }

    /**
     * 删除退货原因类型
     */
    public function deleteType(int $id): bool
    {
        $type = BackorderType::findOrFail($id);

        // 检查是否有关联的退货单
        $count = Backorder::where('reason_type_id', $id)->count();
        if ($count > 0) {
            throw new \Exception('该退货原因已被使用，无法删除');
        }

        return $type->delete();
    }
}
