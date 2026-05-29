<?php

namespace App\Services\Complaint;

use App\Models\Approve\Complaint;
use App\Models\Complaint\ComplaintType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 投诉管理服务层
 */
class ComplaintService
{
    /**
     * 投诉列表
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = Complaint::with(['order', 'type'])
            ->when($params['canteen_name'] ?? null, function ($q, $name) {
                $q->whereHas('order', function ($q) use ($name) {
                    $q->where('canteen_name', 'like', "%{$name}%");
                });
            })
            ->when($params['process_status'] ?? null, function ($q, $status) {
                $q->where('process_status', $status);
            })
            ->when($params['review_status'] ?? null, function ($q, $status) {
                $q->where('review_status', $status);
            })
            ->when($params['type_id'] ?? null, function ($q, $typeId) {
                $q->where('type_id', $typeId);
            })
            ->when($params['start_date'] ?? null, function ($q, $date) {
                $q->where('add_time', '>=', strtotime($date));
            })
            ->when($params['end_date'] ?? null, function ($q, $date) {
                $q->where('add_time', '<=', strtotime($date . ' 23:59:59'));
            });

        $query->orderBy('id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'order_sn' => $item->order?->order_sn,
                    'canteen_name' => $item->order?->canteen_name,
                    'supp_name' => $item->order?->supp_name,
                    'send_date' => $item->order?->send_date,
                    'type_id' => $item->type_id,
                    'type_name' => $item->type?->name,
                    'content' => $item->content,
                    'logo' => $item->logo ? json_decode($item->logo, true) : [],
                    'add_time' => date('Y-m-d H:i:s', $item->add_time),
                    'process_status' => $item->process_status,
                    'process_status_text' => $item->process_status == 1 ? '已处理' : '未处理',
                    'process_remark' => $item->process_remark,
                    'process_user' => $item->process_user,
                    'process_time' => $item->process_time ? date('Y-m-d H:i:s', $item->process_time) : null,
                    'review_status' => $item->review_status,
                    'review_status_text' => $item->review_status == 1 ? '已审阅' : '未审阅',
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 投诉详情
     */
    public function getDetail(int $id): array
    {
        $complaint = Complaint::with(['order', 'type'])->findOrFail($id);

        return [
            'id' => $complaint->id,
            'order_id' => $complaint->order_id,
            'order_sn' => $complaint->order?->order_sn,
            'canteen_name' => $complaint->order?->canteen_name,
            'supp_name' => $complaint->order?->supp_name,
            'send_date' => $complaint->order?->send_date,
            'type_id' => $complaint->type_id,
            'type_name' => $complaint->type?->name,
            'content' => $complaint->content,
            'logo' => $complaint->logo ? json_decode($complaint->logo, true) : [],
            'add_time' => date('Y-m-d H:i:s', $complaint->add_time),
            'process_status' => $complaint->process_status,
            'process_remark' => $complaint->process_remark,
            'process_user' => $complaint->process_user,
            'process_time' => $complaint->process_time ? date('Y-m-d H:i:s', $complaint->process_time) : null,
            'review_status' => $complaint->review_status,
        ];
    }

    /**
     * 处理投诉
     */
    public function process(int $id, array $data): Complaint
    {
        $complaint = Complaint::findOrFail($id);

        if ($complaint->process_status == 1) {
            throw new \Exception('该投诉已处理');
        }

        $complaint->process_status = 1;
        $complaint->process_remark = $data['remark'] ?? '';
        $complaint->process_user = Auth::user()->name ?? '';
        $complaint->process_time = time();
        $complaint->save();

        return $complaint;
    }

    /**
     * 投诉类型列表
     */
    public function getTypeList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = ComplaintType::when($params['name'] ?? null, function ($q, $name) {
                $q->where('name', 'like', "%{$name}%");
            })
            ->when($params['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            });

        $query->orderBy('sort', 'asc')->orderBy('id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 新增投诉类型
     */
    public function createType(array $data): ComplaintType
    {
        return ComplaintType::create([
            'name' => $data['name'],
            'home' => $data['home'] ?? 0,
            'sort' => $data['sort'] ?? 0,
            'status' => $data['status'] ?? 1,
        ]);
    }

    /**
     * 编辑投诉类型
     */
    public function updateType(int $id, array $data): ComplaintType
    {
        $type = ComplaintType::findOrFail($id);
        $type->update($data);
        return $type;
    }
}