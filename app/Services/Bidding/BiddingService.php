<?php

namespace App\Services\Bidding;

use App\Models\Bidding\BiddingHistory;
use App\Models\Bidding\BiddingLog;
use App\Models\Bidding\DiscountLog;
use App\Support\BiddingLogHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 招投标服务层
 */
class BiddingService
{
    /**
     * 获取合作申请列表
     */
    public function getHistoryList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = BiddingHistory::with(['school', 'canteen', 'supplier', 'auditor'])
            ->search($params['keyword'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->byCanteen($params['canteen_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byAuditStatus($params['audit_status'] ?? null)
            ->byType($params['type'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

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
                    'school_id' => $item->school_id,
                    'school_name' => $item->school?->school_name,
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'supp_id' => $item->supp_id,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'type' => $item->type,
                    'type_text' => $item->getTypeText(),
                    'audit_status' => $item->audit_status,
                    'audit_status_text' => $item->getAuditStatusText(),
                    'start_date' => $item->start_date?->format('Y-m-d'),
                    'end_date' => $item->end_date?->format('Y-m-d'),
                    'auditor_name' => $item->auditor?->realname,
                    'audit_time' => $item->audit_time?->format('Y-m-d H:i:s'),
                    'attachments' => $item->attachments ?? [],
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取申请详情
     */
    public function getHistoryDetail(int $id): array
    {
        $history = BiddingHistory::with(['school', 'canteen', 'supplier', 'auditor'])
            ->findOrFail($id);

        return [
            'id' => $history->id,
            'school_id' => $history->school_id,
            'school_name' => $history->school?->school_name,
            'canteen_id' => $history->canteen_id,
            'canteen_name' => $history->canteen?->canteen_name,
            'supp_id' => $history->supp_id,
            'supplier_name' => $history->supplier?->supplier_name,
            'type' => $history->type,
            'type_text' => $history->getTypeText(),
            'audit_status' => $history->audit_status,
            'audit_status_text' => $history->getAuditStatusText(),
            'start_date' => $history->start_date?->format('Y-m-d'),
            'end_date' => $history->end_date?->format('Y-m-d'),
            'attachments' => $history->attachments ?? [],
            'remark' => $history->remark,
            'audit_remark' => $history->audit_remark,
            'auditor_id' => $history->auditor_id,
            'auditor_name' => $history->auditor?->realname,
            'audit_time' => $history->audit_time?->format('Y-m-d H:i:s'),
            'created_at' => $history->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 审核合作申请
     */
    public function audit(int $id, int $auditStatus, string $auditRemark = ''): BiddingHistory
    {
        DB::beginTransaction();
        try {
            $history = BiddingHistory::findOrFail($id);

            // 检查是否已审核
            if ($history->audit_status !== BiddingHistory::AUDIT_STATUS_PENDING) {
                throw new \Exception('该申请已审核，不能重复操作');
            }

            // 更新审核状态
            $history->update([
                'audit_status' => $auditStatus,
                'audit_remark' => $auditRemark,
                'audit_time' => now(),
                'auditor_id' => Auth::id(),
            ]);

            // 如果审核通过，创建或更新合作关系
            if ($auditStatus === BiddingHistory::AUDIT_STATUS_APPROVED) {
                $this->createOrUpdateBiddingLog($history);
            }

            DB::commit();
            return $history->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 创建或更新合作关系
     */
    private function createOrUpdateBiddingLog(BiddingHistory $history): BiddingLog
    {
        $query = BiddingLog::where('supp_id', $history->supp_id)
            ->where('canteen_id', $history->canteen_id);

        if (BiddingLogHelper::hasSchoolIdColumn() && $history->school_id) {
            $query->where('school_id', $history->school_id);
        }

        $existingLog = $query->first();

        $updatePayload = ['status' => BiddingLog::STATUS_ACTIVE];
        $createPayload = [
            'supp_id' => $history->supp_id,
            'canteen_id' => $history->canteen_id,
            'status' => BiddingLog::STATUS_ACTIVE,
            'add_time' => time(),
        ];

        if (BiddingLogHelper::hasSchoolIdColumn()) {
            $updatePayload['effective_status'] = BiddingLog::EFFECTIVE_ACTIVE;
            $updatePayload['start_date'] = $history->start_date;
            $updatePayload['end_date'] = $history->end_date;
            $createPayload['school_id'] = $history->school_id;
            $createPayload['effective_status'] = BiddingLog::EFFECTIVE_ACTIVE;
            $createPayload['start_date'] = $history->start_date;
            $createPayload['end_date'] = $history->end_date;
        }

        if ($existingLog) {
            $existingLog->update($updatePayload);

            return $existingLog->fresh();
        }

        return BiddingLog::create($createPayload);
    }

    /**
     * 获取合作关系列表
     */
    public function getLogList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = BiddingLog::with(['school', 'canteen', 'supplier'])
            ->search($params['keyword'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->byCanteen($params['canteen_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->byEffectiveStatus($params['effective_status'] ?? null);

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
                    'school_id' => $item->school_id,
                    'school_name' => $item->school?->school_name,
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'supp_id' => $item->supp_id,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'status' => $item->status,
                    'status_text' => $item->getStatusText(),
                    'effective_status' => $item->effective_status,
                    'effective_status_text' => $item->getEffectiveStatusText(),
                    'start_date' => $item->start_date?->format('Y-m-d'),
                    'end_date' => $item->end_date?->format('Y-m-d'),
                    'remark' => $item->remark,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取供应商报价列表
     */
    public function getDiscountList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = DiscountLog::with(['goods', 'supplier', 'school', 'canteen'])
            ->search($params['keyword'] ?? null)
            ->byGoods($params['goods_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->byCanteen($params['canteen_id'] ?? null);

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
                    'goods_id' => $item->goods_id,
                    'goods_name' => $item->goods?->goods_name,
                    'goods_unit' => $item->goods?->unit,
                    'goods_spec' => $item->goods?->spec,
                    'supp_id' => $item->supp_id,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'school_id' => $item->school_id,
                    'school_name' => $item->school?->school_name,
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'quotation_price' => $item->quotation_price,
                    'limit_price' => $item->limit_price,
                    'discount' => $item->discount,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 获取报价历史记录
     */
    public function getDiscountHistory(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = DiscountLog::with(['goods', 'supplier', 'school', 'canteen'])
            ->byGoods($params['goods_id'] ?? null)
            ->bySupplier($params['supplier_id'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->byCanteen($params['canteen_id'] ?? null)
            ->byDateRange($params['start_date'] ?? null, $params['end_date'] ?? null);

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
                    'goods_id' => $item->goods_id,
                    'goods_name' => $item->goods?->goods_name,
                    'goods_unit' => $item->goods?->unit,
                    'goods_spec' => $item->goods?->spec,
                    'supp_id' => $item->supp_id,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'school_id' => $item->school_id,
                    'school_name' => $item->school?->school_name,
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'quotation_price' => $item->quotation_price,
                    'limit_price' => $item->limit_price,
                    'discount' => $item->discount,
                    'remark' => $item->remark,
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 终止合作关系
     */
    public function terminate(int $logId, string $remark = ''): BiddingLog
    {
        DB::beginTransaction();
        try {
            $log = BiddingLog::findOrFail($logId);

            // 更新合作关系状态
            $log->update([
                'status' => BiddingLog::STATUS_TERMINATED,
                'effective_status' => BiddingLog::EFFECTIVE_EXPIRED,
                'remark' => $remark,
            ]);

            DB::commit();
            return $log->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
