<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Approve\ApproveService;
use App\Http\Requests\Admin\Approve\ReviewRequest;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * 审批管理控制器
 */
class ApproveController extends Controller
{
    protected ApproveService $approveService;

    public function __construct(ApproveService $approveService)
    {
        $this->approveService = $approveService;
    }

    /**
     * 评论列表
     */
    public function commentIndex(Request $request)
    {
        $params = $request->only([
            'review_status',
            'canteen_name',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->approveService->getCommentList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 评论审阅
     */
    public function commentReview(ReviewRequest $request)
    {
        $id = $request->input('id');
        $userId = Auth::id();

        try {
            $comment = $this->approveService->reviewComment($id, $userId);

            return ResponseHelper::success([
                'id' => $comment->id,
                'review_status' => $comment->review_status,
                'review_status_text' => $comment->getReviewStatusText(),
            ], '审阅成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }

    /**
     * 投诉列表
     */
    public function complaintIndex(Request $request)
    {
        $params = $request->only([
            'review_status',
            'process_status',
            'canteen_name',
            'contact_name',
            'contact_phone',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->approveService->getComplaintList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 投诉审阅
     */
    public function complaintReview(ReviewRequest $request)
    {
        $id = $request->input('id');
        $userId = Auth::id();

        try {
            $complaint = $this->approveService->reviewComplaint($id, $userId);

            return ResponseHelper::success([
                'id' => $complaint->id,
                'review_status' => $complaint->review_status,
                'review_status_text' => $complaint->getReviewStatusText(),
            ], '审阅成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }

    /**
     * 合作申请列表
     */
    public function biddingIndex(Request $request)
    {
        $params = $request->only([
            'review_status',
            'audit_status',
            'school_id',
            'emergency_contact',
            'emergency_phone',
            'start_date',
            'end_date',
            'page',
            'page_size',
            'sort_field',
            'sort_order',
        ]);

        $result = $this->approveService->getBiddingList($params);

        return ResponseHelper::success($result);
    }

    /**
     * 合作审阅
     */
    public function biddingReview(ReviewRequest $request)
    {
        $id = $request->input('id');
        $userId = Auth::id();

        try {
            $bidding = $this->approveService->reviewBidding($id, $userId);

            return ResponseHelper::success([
                'id' => $bidding->id,
                'review_status' => $bidding->review_status,
                'review_status_text' => $bidding->getReviewStatusText(),
            ], '审阅成功');
        } catch (\Exception $e) {
            return ResponseHelper::error(40004, $e->getMessage());
        }
    }
}
