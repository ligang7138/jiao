<?php

namespace App\Services\Emergency;

use App\Models\Emergency\Emergency;
use App\Models\Emergency\EmergencyType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * 应急管理服务层
 */
class EmergencyService
{
    /**
     * 应急事件列表
     */
    public function getList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = Emergency::with(['canteen.school', 'type'])
            ->when($params['canteen_name'] ?? null, function ($q, $name) {
                $q->whereHas('canteen', function ($q) use ($name) {
                    $q->where('name', 'like', "%{$name}%");
                });
            })
            ->when($params['process_status'] ?? null, function ($q, $status) {
                $q->where('process_status', $status);
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
                    'canteen_id' => $item->canteen_id,
                    'canteen_name' => $item->canteen?->name,
                    'school_name' => $item->canteen?->school?->school_name,
                    'type_id' => $item->type_id,
                    'type_name' => $item->type_name,
                    'linkman' => $item->linkman,
                    'mobile' => $item->mobile,
                    'content' => $item->content,
                    'logo' => $item->logo ? json_decode($item->logo, true) : [],
                    'add_time' => date('Y-m-d H:i:s', $item->add_time),
                    'process_status' => $item->process_status,
                    'process_status_text' => $item->process_status == 1 ? '已处理' : '未处理',
                    'process_remark' => $item->process_remark,
                    'process_user' => $item->process_user,
                    'process_time' => $item->process_time ? date('Y-m-d H:i:s', $item->process_time) : null,
                ];
            }),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    /**
     * 应急事件详情
     */
    public function getDetail(int $id): array
    {
        $emergency = Emergency::with(['canteen.school', 'type'])->findOrFail($id);

        return [
            'id' => $emergency->id,
            'canteen_id' => $emergency->canteen_id,
            'canteen_name' => $emergency->canteen?->name,
            'school_name' => $emergency->canteen?->school?->school_name,
            'type_id' => $emergency->type_id,
            'type_name' => $emergency->type_name,
            'linkman' => $emergency->linkman,
            'mobile' => $emergency->mobile,
            'content' => $emergency->content,
            'logo' => $emergency->logo ? json_decode($emergency->logo, true) : [],
            'add_time' => date('Y-m-d H:i:s', $emergency->add_time),
            'process_status' => $emergency->process_status,
            'process_status_text' => $emergency->process_status == 1 ? '已处理' : '未处理',
            'process_remark' => $emergency->process_remark,
            'process_user' => $emergency->process_user,
            'process_time' => $emergency->process_time ? date('Y-m-d H:i:s', $emergency->process_time) : null,
        ];
    }

    /**
     * 处理应急事件
     */
    public function process(int $id, array $data): Emergency
    {
        $emergency = Emergency::findOrFail($id);

        if ($emergency->process_status == 1) {
            throw new \Exception('该应急事件已处理');
        }

        $emergency->process_status = 1;
        $emergency->process_remark = $data['remark'] ?? '';
        $emergency->process_user = Auth::user()->name ?? '';
        $emergency->process_time = time();
        $emergency->save();

        return $emergency;
    }

    /**
     * 应急类型列表
     */
    public function getTypeList(array $params): array
    {
        $page = $params['page'] ?? 1;
        $pageSize = $params['page_size'] ?? 20;

        $query = EmergencyType::when($params['name'] ?? null, function ($q, $name) {
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
     * 新增应急类型
     */
    public function createType(array $data): EmergencyType
    {
        return EmergencyType::create([
            'name' => $data['name'],
            'home' => $data['home'] ?? 0,
            'sort' => $data['sort'] ?? 0,
            'status' => $data['status'] ?? 1,
        ]);
    }

    /**
     * 编辑应急类型
     */
    public function updateType(int $id, array $data): EmergencyType
    {
        $type = EmergencyType::findOrFail($id);
        $type->update($data);
        return $type;
    }
}