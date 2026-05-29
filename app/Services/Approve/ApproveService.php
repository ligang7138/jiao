<?php

namespace App\Services\Approve;

use App\Models\Approve\Comment;
use App\Models\Approve\Complaint;
use App\Models\Bidding\BiddingHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 审批服务层
 */
class ApproveService
{
    /**
     * 获取评论列表
     */
    public function getCommentList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = Comment::with(['school', 'canteen', 'order', 'user'])
            ->byReviewStatus($params['review_status'] ?? null)
            ->byCanteenName($params['canteen_name'] ?? null)
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
                    'order_id' => $item->order_id,
                    'order_no' => $item->order?->order_no,
                    'school_name' => $item->school?->school_name,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'content' => $item->content,
                    'images' => $item->images ?? [],
                    'user_name' => $item->user_name,
                    'service_score' => $item->service_score,
                    'delivery_score' => $item->delivery_score,
                    'quality_score' => $item->quality_score,
                    'price_score' => $item->price_score,
                    'review_status' => $item->review_status,
                    'review_status_text' => $item->getReviewStatusText(),
                    'review_time' => $item->review_time?->format('Y-m-d H:i:s'),
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 评论审阅
     */
    public function reviewComment(int $id, int $userId): Comment
    {
        DB::beginTransaction();
        try {
            $comment = Comment::findOrFail($id);

            if ($comment->isReviewed()) {
                throw new \Exception('该评论已审阅，请勿重复操作');
            }

            $comment->update([
                'review_status' => Comment::REVIEW_STATUS_REVIEWED,
                'review_time' => now(),
                'review_user_id' => $userId,
            ]);

            DB::commit();
            return $comment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 获取投诉列表
     */
    public function getComplaintList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = Complaint::with(['school', 'canteen', 'supplier', 'order'])
            ->byReviewStatus($params['review_status'] ?? null)
            ->byProcessStatus($params['process_status'] ?? null)
            ->byCanteenName($params['canteen_name'] ?? null)
            ->byContactName($params['contact_name'] ?? null)
            ->byContactPhone($params['contact_phone'] ?? null)
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
                    'order_id' => $item->order_id,
                    'order_no' => $item->order?->order_no,
                    'school_name' => $item->school?->school_name,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'type_name' => $item->type_name,
                    'content' => $item->content,
                    'images' => $item->images ?? [],
                    'contact_name' => $item->contact_name,
                    'contact_phone' => $item->contact_phone,
                    'process_status' => $item->process_status,
                    'process_status_text' => $item->getProcessStatusText(),
                    'review_status' => $item->review_status,
                    'review_status_text' => $item->getReviewStatusText(),
                    'review_time' => $item->review_time?->format('Y-m-d H:i:s'),
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 投诉审阅
     */
    public function reviewComplaint(int $id, int $userId): Complaint
    {
        DB::beginTransaction();
        try {
            $complaint = Complaint::findOrFail($id);

            if ($complaint->isReviewed()) {
                throw new \Exception('该投诉已审阅，请勿重复操作');
            }

            $complaint->update([
                'review_status' => Complaint::REVIEW_STATUS_REVIEWED,
                'review_time' => now(),
                'review_user_id' => $userId,
            ]);

            DB::commit();
            return $complaint->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 获取合作申请列表
     */
    public function getBiddingList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = BiddingHistory::with(['school', 'canteen', 'supplier'])
            ->byReviewStatus($params['review_status'] ?? null)
            ->byAuditStatus($params['audit_status'] ?? null)
            ->bySchool($params['school_id'] ?? null)
            ->byEmergencyContact($params['emergency_contact'] ?? null)
            ->byEmergencyPhone($params['emergency_phone'] ?? null)
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
                    'school_name' => $item->school?->school_name,
                    'canteen_name' => $item->canteen?->canteen_name,
                    'supplier_name' => $item->supplier?->supplier_name,
                    'type' => $item->type,
                    'type_text' => $item->getTypeText(),
                    'emergency_contact' => $item->emergency_contact,
                    'emergency_phone' => $item->emergency_phone,
                    'attachments' => $item->getAttachmentUrls(),
                    'audit_status' => $item->audit_status,
                    'audit_status_text' => $item->getAuditStatusText(),
                    'audit_reason' => $item->audit_remark,
                    'review_status' => $item->review_status,
                    'review_status_text' => $item->getReviewStatusText(),
                    'review_time' => $item->review_time?->format('Y-m-d H:i:s'),
                    'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 合作审阅
     */
    public function reviewBidding(int $id, int $userId): BiddingHistory
    {
        DB::beginTransaction();
        try {
            $bidding = BiddingHistory::findOrFail($id);

            if ($bidding->isReviewed()) {
                throw new \Exception('该合作申请已审阅，请勿重复操作');
            }

            $bidding->update([
                'review_status' => BiddingHistory::REVIEW_STATUS_REVIEWED,
                'review_time' => now(),
                'review_user_id' => $userId,
            ]);

            DB::commit();
            return $bidding->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
