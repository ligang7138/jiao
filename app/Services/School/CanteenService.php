<?php

namespace App\Services\School;

use App\Models\School\Canteen;
use App\Models\School\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 食堂服务层（对齐旧 school_canteen/ajax.php 业务逻辑）
 */
class CanteenService
{
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = DB::table('school_canteen as sc')
            ->join('school as s', 's.id', '=', 'sc.school_id');

        if (Schema::hasTable('school_canteen_data')) {
            $query->leftJoin('school_canteen_data as sd', 'sc.id', '=', 'sd.canteen_id');
        }

        if (!empty($params['name'])) {
            $query->where('sc.name', 'like', '%' . trim($params['name']) . '%');
        }

        if (!empty($params['keyword'])) {
            $query->where('sc.name', 'like', '%' . trim($params['keyword']) . '%');
        }

        if (!empty($params['canteen_sn'])) {
            $query->where('sc.canteen_sn', trim($params['canteen_sn']));
        }

        if (!empty($params['school_name'])) {
            $query->where('s.school_name', 'like', '%' . trim($params['school_name']) . '%');
        }

        if (!empty($params['school_district'])) {
            $query->where('s.school_district', trim($params['school_district']));
        }

        if (!empty($params['school_sn'])) {
            $query->where('s.school_sn', 'like', '%' . trim($params['school_sn']) . '%');
        }

        if (!empty($params['emergency_linkman'])) {
            $query->where('sc.emergency_linkman', 'like', '%' . trim($params['emergency_linkman']) . '%');
        }

        if (!empty($params['emergency_mobile'])) {
            $query->where('sc.emergency_mobile', 'like', '%' . trim($params['emergency_mobile']) . '%');
        }

        if (!empty($params['receive_linkman'])) {
            $query->where('sc.receive_linkman', 'like', '%' . trim($params['receive_linkman']) . '%');
        }

        if (!empty($params['receive_mobile'])) {
            $query->where('sc.receive_mobile', 'like', '%' . trim($params['receive_mobile']) . '%');
        }

        if (isset($params['canteen_type']) && $params['canteen_type'] !== '' && $params['canteen_type'] !== null) {
            $query->where('sc.canteen_type', (int) $params['canteen_type']);
        }

        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== null) {
            $query->where('sc.status', (int) $params['status']);
        }

        if (!empty($params['school_id'])) {
            $query->where('sc.school_id', (int) $params['school_id']);
        }

        if (isset($params['group_id']) && $params['group_id'] !== '' && $params['group_id'] !== null) {
            if ((int) $params['group_id'] === 0) {
                $query->where(function ($q) {
                    $q->whereNull('sc.group_id')->orWhere('sc.group_id', 0);
                });
            } else {
                $query->where('sc.group_id', (int) $params['group_id']);
            }
        }

        $total = (clone $query)->count();

        $select = [
            'sc.*',
            's.school_name',
            's.school_sn',
            's.school_district',
        ];

        if (Schema::hasTable('school_canteen_data')) {
            $select[] = 'sd.current_month_purchase_amount';
        }

        $rows = $query
            ->select($select)
            ->orderByDesc('sc.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'list' => $rows->map(function ($item) {
                $canteen = (new Canteen())->forceFill((array) $item);
                $monthly = (float) ($item->monthly_purchase_amount ?? 0);
                $current = (float) ($item->current_month_purchase_amount ?? 0);
                $percentage = 0;

                if ($monthly > 0) {
                    $percentage = round($current / 10000 / $monthly * 100, 2);
                }

                return [
                    'id' => (int) $item->id,
                    'school_id' => (int) $item->school_id,
                    'canteen_sn' => $item->canteen_sn,
                    'name' => $item->name,
                    'school_name' => $item->school_name,
                    'school_sn' => $item->school_sn,
                    'school_district' => $item->school_district,
                    'emergency_linkman' => $item->emergency_linkman,
                    'emergency_mobile' => $item->emergency_mobile,
                    'receive_linkman' => $item->receive_linkman,
                    'receive_mobile' => $item->receive_mobile,
                    'address' => $item->address,
                    'status' => (int) $item->status,
                    'status_text' => $canteen->getStatusText(),
                    'canteen_type' => (int) $item->canteen_type,
                    'canteen_type_text' => $canteen->getCanteenTypeText(),
                    'monthly_purchase_amount' => $monthly,
                    'purchase_percentage' => $percentage,
                    'group_id' => (int) ($item->group_id ?? 0),
                    'is_audit' => (int) ($item->is_audit ?? 0),
                    'add_time' => $item->add_time ? date('Y-m-d', (int) $item->add_time) : null,
                ];
            })->values()->all(),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function create(array $data): Canteen
    {
        $payload = $this->normalizePayload($data);
        $this->validatePayload($payload, true);

        return DB::transaction(function () use ($payload) {
            $canteen = Canteen::create(array_merge($payload, $this->legacyCreateDefaults(), [
                'add_user' => auth()->user()->name ?? '',
                'add_time' => time(),
            ]));

            $canteenSn = 10000 + (int) $canteen->id;
            $canteen->update(['canteen_sn' => (string) $canteenSn]);

            return $canteen->fresh(['school']);
        });
    }

    public function update(int $id, array $data): Canteen
    {
        $canteen = Canteen::find($id);
        if (!$canteen) {
            throw new \InvalidArgumentException('记录不存在');
        }

        $payload = $this->normalizePayload(array_merge($canteen->toArray(), $data));
        $this->validatePayload($payload, false, $id);

        $canteen->update($this->withUpdateTime($payload));

        return $canteen->fresh(['school']);
    }

    public function setStatus(int $id, int $status): Canteen
    {
        if (!in_array($status, [Canteen::STATUS_ENABLED, Canteen::STATUS_DISABLED], true)) {
            throw new \InvalidArgumentException('状态错误');
        }

        $canteen = Canteen::find($id);
        if (!$canteen) {
            throw new \InvalidArgumentException('记录不存在');
        }

        $canteen->update($this->withUpdateTime([
            'status' => $status,
        ]));

        return $canteen->fresh(['school']);
    }

    public function getDetail(int $id): array
    {
        $canteen = Canteen::with('school')->find($id);
        if (!$canteen) {
            throw new \InvalidArgumentException('记录不存在');
        }

        return $this->formatDetail($canteen);
    }

    public function getBySchool(int $schoolId): array
    {
        return Canteen::query()
            ->where('school_id', $schoolId)
            ->where('status', Canteen::STATUS_ENABLED)
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'school_id' => $item->school_id,
                'school_name' => $item->school?->school_name,
            ])
            ->all();
    }

    public function getActiveCanteens(array $params = []): array
    {
        $query = Canteen::query()
            ->with('school')
            ->where('status', Canteen::STATUS_ENABLED);

        if (!empty($params['exclude_grouped'])) {
            $query->where(function ($q) {
                $q->whereNull('group_id')->orWhere('group_id', 0);
            });
        }

        return $query
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'school_id' => $item->school_id,
                'school_name' => $item->school?->school_name,
                'canteen_type' => (int) $item->canteen_type,
                'canteen_type_text' => $item->getCanteenTypeText(),
            ])
            ->all();
    }

    private function normalizePayload(array $data): array
    {
        return [
            'school_id' => (int) ($data['school_id'] ?? 0),
            'name' => trim($data['name'] ?? ''),
            'linkman' => trim($data['linkman'] ?? ''),
            'mobile' => trim($data['mobile'] ?? ''),
            'receive_linkman' => trim($data['receive_linkman'] ?? ''),
            'receive_mobile' => trim($data['receive_mobile'] ?? ''),
            'receive_start_time' => trim($data['receive_start_time'] ?? '06:00'),
            'receive_end_time' => trim($data['receive_end_time'] ?? '08:00'),
            'emergency_linkman' => trim($data['emergency_linkman'] ?? ''),
            'emergency_mobile' => trim($data['emergency_mobile'] ?? ''),
            'code' => trim($data['code'] ?? ''),
            'credit_code' => trim($data['credit_code'] ?? ''),
            'canteen_type' => (int) ($data['canteen_type'] ?? 1),
            'status' => (int) ($data['status'] ?? Canteen::STATUS_ENABLED),
            'address' => trim($data['address'] ?? ''),
            'remark' => trim($data['remark'] ?? ''),
            'monthly_purchase_amount' => (float) ($data['monthly_purchase_amount'] ?? 0),
        ];
    }

    private function validatePayload(array $payload, bool $isCreate, int $excludeId = 0): void
    {
        if ($payload['school_id'] <= 0) {
            throw new \InvalidArgumentException('请选择所属学校');
        }

        if ($payload['name'] === '') {
            throw new \InvalidArgumentException('请输入食堂名称');
        }

        if (!in_array($payload['status'], [Canteen::STATUS_ENABLED, Canteen::STATUS_DISABLED], true)) {
            throw new \InvalidArgumentException('状态错误');
        }

        if ($payload['monthly_purchase_amount'] <= 0) {
            throw new \InvalidArgumentException('请输入月计划（万元）');
        }

        if ($payload['monthly_purchase_amount'] > 99999999.99) {
            throw new \InvalidArgumentException('月计划（万元）不能超过99999999.99');
        }

        if (!School::where('id', $payload['school_id'])->exists()) {
            throw new \InvalidArgumentException('所属学校不存在');
        }

        if (!$isCreate && Canteen::where('name', $payload['name'])->where('id', '<>', $excludeId)->exists()) {
            throw new \InvalidArgumentException('食堂名称已存在，添加失败');
        }

        if ($isCreate && Canteen::where('name', $payload['name'])->exists()) {
            throw new \InvalidArgumentException('食堂名称已存在，添加失败');
        }
    }

    /**
     * 旧库 school_canteen 部分 NOT NULL 字段无默认值，新增时需补齐空串。
     *
     * @return array<string, mixed>
     */
    private function legacyCreateDefaults(): array
    {
        return [
            'username' => '',
            'password' => '',
            'linkman' => '',
            'mobile' => '',
            'address' => '',
            'code' => '',
            'remark' => '',
            'canteen_sn' => '',
        ];
    }

    /**
     * 旧库 school_canteen 表可能不存在 update_time 字段。
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withUpdateTime(array $payload): array
    {
        if (Schema::hasColumn('school_canteen', 'update_time')) {
            $payload['update_time'] = time();
        }

        return $payload;
    }

    private function formatDetail(Canteen $canteen): array
    {
        return [
            'id' => $canteen->id,
            'school_id' => $canteen->school_id,
            'school_name' => $canteen->school?->school_name,
            'canteen_sn' => $canteen->canteen_sn,
            'name' => $canteen->name,
            'linkman' => $canteen->linkman,
            'mobile' => $canteen->mobile,
            'receive_linkman' => $canteen->receive_linkman,
            'receive_mobile' => $canteen->receive_mobile,
            'receive_start_time' => $canteen->receive_start_time,
            'receive_end_time' => $canteen->receive_end_time,
            'emergency_linkman' => $canteen->emergency_linkman,
            'emergency_mobile' => $canteen->emergency_mobile,
            'code' => $canteen->code,
            'credit_code' => $canteen->credit_code,
            'canteen_type' => (int) $canteen->canteen_type,
            'canteen_type_text' => $canteen->getCanteenTypeText(),
            'status' => (int) $canteen->status,
            'status_text' => $canteen->getStatusText(),
            'address' => $canteen->address,
            'remark' => $canteen->remark,
            'monthly_purchase_amount' => (float) $canteen->monthly_purchase_amount,
            'add_time' => $canteen->add_time ? date('Y-m-d H:i:s', (int) $canteen->add_time) : null,
        ];
    }
}
