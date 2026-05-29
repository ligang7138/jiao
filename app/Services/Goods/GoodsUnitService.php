<?php

namespace App\Services\Goods;

use App\Models\Goods\Goods;
use App\Models\Goods\GoodsUnit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 商品单位服务层
 */
class GoodsUnitService
{
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = GoodsUnit::query();

        if ($name = trim((string) ($params['name'] ?? ''))) {
            $query->where('name', 'like', "%{$name}%");
        }

        if (($status = $params['status'] ?? '') !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->orderBy('id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(fn ($item) => $this->formatUnit($item))
            ->values()
            ->all();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function create(array $data): GoodsUnit
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new \InvalidArgumentException('单位名称不能为空');
        }

        if (GoodsUnit::where('name', $name)->exists()) {
            throw new \InvalidArgumentException('单位已存在');
        }

        $user = Auth::user();
        $now = time();

        return GoodsUnit::create([
            'name' => $name,
            'status' => GoodsUnit::STATUS_ON,
            'add_user' => $user?->name ?? '',
            'add_time' => $now,
        ]);
    }

    public function update(int $id, array $data): GoodsUnit
    {
        $unit = GoodsUnit::findOrFail($id);
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            throw new \InvalidArgumentException('单位名称不能为空');
        }

        if (GoodsUnit::where('name', $name)->where('id', '!=', $id)->exists()) {
            throw new \InvalidArgumentException('单位已存在');
        }

        $user = Auth::user();
        $unit->update([
            'name' => $name,
            'update_user' => $user?->name ?? '',
            'update_time' => time(),
        ]);

        return $unit->fresh();
    }

    public function changeStatus(int $id, int $status): GoodsUnit
    {
        if (!in_array($status, [0, 1], true)) {
            throw new \InvalidArgumentException('状态错误');
        }

        $unit = GoodsUnit::findOrFail($id);

        if ($status === GoodsUnit::STATUS_OFF) {
            $exists = Goods::where('unit', $unit->name)->exists();
            if ($exists) {
                throw new \InvalidArgumentException('单位下有绑定商品数据时，不允许停用');
            }
        }

        $unit->status = $status;
        $unit->save();

        return $unit;
    }

    public function getActiveUnits(): array
    {
        return GoodsUnit::where('status', GoodsUnit::STATUS_ON)
            ->orderBy('id')
            ->get(['id', 'name'])
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->all();
    }

    private function formatUnit(GoodsUnit $item): array
    {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'status' => (int) $item->status,
            'add_user' => $item->add_user ?? '',
            'add_time' => (int) ($item->add_time ?? 0),
            'update_user' => $item->update_user ?? '',
            'update_time' => (int) ($item->update_time ?? 0),
        ];
    }
}
