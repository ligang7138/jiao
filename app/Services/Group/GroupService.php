<?php

namespace App\Services\Group;

use App\Models\Group\Group;
use App\Models\School\Canteen;
use Illuminate\Support\Facades\DB;

/**
 * 分组管理服务层（对齐旧 group/ajax.php 业务逻辑）
 */
class GroupService
{
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = Group::query();

        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . trim($params['name']) . '%');
        }

        if (isset($params['id']) && $params['id'] !== '' && $params['id'] !== null) {
            $query->where('id', (int) $params['id']);
        } elseif (isset($params['pid']) && $params['pid'] !== '' && $params['pid'] !== null) {
            $query->where('pid', (int) $params['pid']);
        }

        if (isset($params['status']) && $params['status'] !== '' && $params['status'] !== null) {
            $query->where('status', (int) $params['status']);
        }

        $total = (clone $query)->count();
        $list = $query->orderByDesc('id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $groupIds = $list->pluck('id')->all();
        $groupIds[] = 0;

        $canteenCounts = Canteen::query()
            ->select('group_id', DB::raw('count(*) as count'))
            ->whereIn('group_id', $groupIds)
            ->groupBy('group_id')
            ->pluck('count', 'group_id')
            ->all();

        $parentNames = Group::query()
            ->whereIn('id', $list->pluck('pid')->filter()->unique()->all())
            ->pluck('name', 'id')
            ->all();

        return [
            'list' => $list->map(function ($item) use ($canteenCounts, $parentNames) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'pid' => (int) $item->pid,
                    'parent_name' => $parentNames[$item->pid] ?? '',
                    'code' => $item->code,
                    'status' => (int) $item->status,
                    'canteen_count' => (int) ($canteenCounts[$item->id] ?? 0),
                    'school_num' => (int) ($canteenCounts[$item->id] ?? 0),
                    'add_user' => $item->add_user,
                    'add_time' => $item->add_time ? date('Y-m-d H:i', (int) $item->add_time) : null,
                    'update_user' => $item->update_user,
                    'update_time' => $item->update_time ? date('Y-m-d H:i', (int) $item->update_time) : null,
                ];
            })->values()->all(),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function getDetail(int $id): array
    {
        $group = Group::find($id);
        if (!$group) {
            throw new \InvalidArgumentException('记录不存在，编辑失败');
        }

        return [
            'id' => $group->id,
            'name' => $group->name,
            'pid' => (int) $group->pid,
            'code' => $group->code,
            'status' => (int) $group->status,
        ];
    }

    public function create(array $data): Group
    {
        $name = trim($data['name'] ?? '');
        $pid = (int) ($data['pid'] ?? 0);
        $code = trim($data['code'] ?? '');

        if ($name === '') {
            throw new \InvalidArgumentException('请输入分组名称');
        }

        if (Group::where('name', $name)->exists()) {
            throw new \InvalidArgumentException('分组名称已存在');
        }

        return Group::create([
            'name' => $name,
            'pid' => $pid,
            'code' => $code,
            'status' => $data['status'] ?? Group::STATUS_ENABLED,
            'add_user' => auth()->user()->name ?? '',
            'add_time' => time(),
            'update_user' => auth()->user()->name ?? '',
            'update_time' => time(),
        ]);
    }

    public function update(int $id, array $data): Group
    {
        $group = Group::find($id);
        if (!$group) {
            throw new \InvalidArgumentException('记录不存在，编辑失败');
        }

        $name = trim($data['name'] ?? $group->name);
        $pid = (int) ($data['pid'] ?? $group->pid);
        $code = trim($data['code'] ?? $group->code);

        if ($pid === $id) {
            throw new \InvalidArgumentException('父分组不能是自己');
        }

        if (Group::where('name', $name)->where('id', '<>', $id)->exists()) {
            throw new \InvalidArgumentException('分组名称已存在');
        }

        $group->update([
            'name' => $name,
            'pid' => $pid,
            'code' => $code,
            'status' => $data['status'] ?? $group->status,
            'update_user' => auth()->user()->name ?? '',
            'update_time' => time(),
        ]);

        return $group->fresh();
    }

    public function delete(int $id): bool
    {
        $group = Group::find($id);
        if (!$group) {
            throw new \InvalidArgumentException('记录不存在');
        }

        if (Group::where('pid', $id)->exists()) {
            throw new \InvalidArgumentException('该分组有子分组，无法删除');
        }

        if (Canteen::where('group_id', $id)->exists()) {
            throw new \InvalidArgumentException('该分组有食堂关联，无法删除');
        }

        return (bool) $group->delete();
    }

    public function getCanteens(int $id): array
    {
        if (!Group::where('id', $id)->exists()) {
            throw new \InvalidArgumentException('分组不存在');
        }

        return Canteen::with(['school'])
            ->where('group_id', $id)
            ->orderByDesc('id')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'school_id' => $item->school_id,
                    'school_name' => $item->school?->school_name,
                    'canteen_type' => (int) $item->canteen_type,
                    'canteen_type_text' => $item->getCanteenTypeText(),
                    'linkman' => $item->linkman,
                    'mobile' => $item->mobile,
                    'is_audit' => (int) $item->is_audit,
                    'is_audit_text' => $item->is_audit === 1 ? '主账号' : '普通账号',
                ];
            })
            ->all();
    }

    public function addCanteen(int $groupId, int $canteenId): void
    {
        if ($canteenId <= 0) {
            throw new \InvalidArgumentException('提交数据错误');
        }

        if (!Group::where('id', $groupId)->exists()) {
            throw new \InvalidArgumentException('分组不存在');
        }

        $canteen = Canteen::find($canteenId);
        if (!$canteen) {
            throw new \InvalidArgumentException('食堂不存在');
        }

        if ((int) $canteen->group_id > 0 && (int) $canteen->group_id !== $groupId) {
            throw new \InvalidArgumentException('该食堂已属于其他分组');
        }

        $canteen->update([
            'group_id' => $groupId,
            'is_audit' => 0,
            'update_time' => time(),
        ]);
    }

    public function removeCanteen(int $groupId, int $canteenId): void
    {
        $canteen = Canteen::where('id', $canteenId)
            ->where('group_id', $groupId)
            ->first();

        if (!$canteen) {
            throw new \InvalidArgumentException('食堂不存在');
        }

        $canteen->update([
            'group_id' => 0,
            'is_audit' => 0,
            'update_time' => time(),
        ]);
    }

    public function setAudit(int $groupId, int $canteenId): void
    {
        DB::transaction(function () use ($groupId, $canteenId) {
            Canteen::where('group_id', $groupId)->update(['is_audit' => 0]);
            Canteen::where('group_id', $groupId)
                ->where('id', $canteenId)
                ->update(['is_audit' => 1, 'update_time' => time()]);
        });
    }

    public function removeAudit(int $groupId, int $canteenId): void
    {
        Canteen::where('id', $canteenId)
            ->where('group_id', $groupId)
            ->update(['is_audit' => 0, 'update_time' => time()]);
    }
}
