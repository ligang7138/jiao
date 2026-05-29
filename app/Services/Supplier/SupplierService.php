<?php

namespace App\Services\Supplier;

use App\Models\Supplier\DiscountLog;
use App\Models\Supplier\Supplier;
use Illuminate\Support\Facades\DB;

/**
 * 供应商服务层（字段与旧库 supplier 表对齐）
 */
class SupplierService
{
    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = Supplier::query()
            ->search($params['keyword'] ?? null)
            ->byStatus($params['status'] ?? null)
            ->byCode($params['code'] ?? null)
            ->byUsername($params['username'] ?? null)
            ->byCateType($params['cate_type'] ?? null);

        $sortField = $params['sort_field'] ?? 'id';
        $allowedSortFields = ['id', 'code', 'name', 'add_time', 'update_time', 'status'];
        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'id';
        }
        $sortOrder = strtolower($params['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortOrder);

        $total = (clone $query)->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $ids = $list->pluck('id')->filter()->all();
        $goodsCounts = $this->countGoodsBySupplierIds($ids);
        $schoolNums = $this->countSchoolsBySupplierIds($ids);

        return [
            'list' => $list->map(fn (Supplier $item) => $this->formatListItem($item, $goodsCounts, $schoolNums)),
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function create(array $data): Supplier
    {
        $payload = $this->mapInputToDatabase($data);
        $payload['status'] = $payload['status'] ?? Supplier::STATUS_DISABLED;
        $payload['add_time'] = time();
        $payload['update_time'] = time();

        if (empty($payload['cate_type'])) {
            $payload['cate_type'] = 1;
        }

        $supplier = Supplier::create($payload);

        if (empty($supplier->code)) {
            $supplier->code = (string) (10000 + (int) $supplier->id);
            $supplier->save();
        }

        return $supplier->fresh();
    }

    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $payload = $this->mapInputToDatabase($data);
        $payload['update_time'] = time();
        $supplier->update($payload);

        return $supplier->fresh();
    }

    public function delete(int $id): bool
    {
        $supplier = Supplier::findOrFail($id);

        if (DB::table('goods_price')->where('supp_id', $id)->exists()) {
            throw new \Exception('该供应商下存在商品报价，无法删除');
        }

        if (DB::table('bidding_log')->where('supp_id', $id)->where('status', 1)->exists()) {
            throw new \Exception('该供应商存在合作学校，无法删除');
        }

        return (bool) $supplier->delete();
    }

    public function changeStatus(int $id, int $status): Supplier
    {
        if (!in_array($status, [Supplier::STATUS_DISABLED, Supplier::STATUS_ENABLED], true)) {
            throw new \InvalidArgumentException('状态值无效，仅支持 0=停用、1=启用');
        }

        $supplier = Supplier::findOrFail($id);
        $supplier->status = $status;
        $supplier->update_time = time();
        $supplier->save();

        return $supplier;
    }

    public function getDetail(int $id): array
    {
        $supplier = Supplier::findOrFail($id);
        $goodsCounts = $this->countGoodsBySupplierIds([$supplier->id]);
        $schoolNums = $this->countSchoolsBySupplierIds([$supplier->id]);

        return $this->formatDetailItem($supplier, $goodsCounts, $schoolNums);
    }

    public function getActiveSuppliers(): array
    {
        return Supplier::query()
            ->where('status', Supplier::STATUS_ENABLED)
            ->orderByRaw('CONVERT(name USING gbk) ASC')
            ->orderBy('id')
            ->get()
            ->map(fn (Supplier $item) => [
                'id' => $item->id,
                'supplier_name' => $item->name,
                'contact_name' => $item->linkman,
                'contact_phone' => $item->mobile,
                'code' => $item->code,
            ])
            ->values()
            ->all();
    }

    public function getDiscountLogs(int $supplierId): array
    {
        Supplier::findOrFail($supplierId);

        return DiscountLog::query()
            ->with('goods:id,goods_name,goods_sn,unit')
            ->where('supp_id', $supplierId)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(function (DiscountLog $item) {
                return [
                    'id' => $item->id,
                    'goods_id' => $item->goods_id,
                    'goods_name' => $item->goods?->goods_name,
                    'goods_sn' => $item->goods?->goods_sn,
                    'quotation_price' => $item->quotation_price,
                    'limit_price' => $item->limit_price,
                    'float_rate' => $item->float_rate,
                    'created_at' => $item->add_time
                        ? date('Y-m-d H:i:s', (int) $item->add_time)
                        : null,
                ];
            })
            ->all();
    }

    private function formatListItem(Supplier $item, array $goodsCounts, array $schoolNums): array
    {
        return [
            'id' => $item->id,
            'code' => $item->code,
            'username' => $item->username,
            'supplier_name' => $item->name,
            'contact_name' => $item->linkman,
            'contact_phone' => $item->mobile,
            'contact_address' => $item->address,
            'company' => $item->company,
            'cate_type' => (int) $item->cate_type,
            'cate_type_text' => (int) $item->cate_type === 1 ? '全品类' : '单品类',
            'status' => (int) $item->status,
            'status_text' => $item->getStatusText(),
            'comment_score' => $item->comment_score,
            'goods_count' => (int) ($goodsCounts[$item->id] ?? 0),
            'school_num' => (int) ($schoolNums[$item->id] ?? 0),
            'created_at' => $item->add_time
                ? date('Y-m-d H:i:s', (int) $item->add_time)
                : null,
        ];
    }

    private function formatDetailItem(Supplier $item, array $goodsCounts, array $schoolNums): array
    {
        return array_merge($this->formatListItem($item, $goodsCounts, $schoolNums), [
            'license_no' => $item->credit_code,
            'license_image' => $item->license_logo,
            'permit_code' => $item->permit_code,
            'permit_logo' => $item->permit_logo,
            'emergency_linkman' => $item->emergency_linkman,
            'emergency_mobile' => $item->emergency_mobile,
            'cate_ids' => $item->cate_ids,
            'updated_at' => $item->update_time
                ? date('Y-m-d H:i:s', (int) $item->update_time)
                : null,
        ]);
    }

    /**
     * Vue API 字段 -> 旧库 supplier 字段
     */
    private function mapInputToDatabase(array $data): array
    {
        $map = [
            'supplier_name' => 'name',
            'contact_name' => 'linkman',
            'contact_phone' => 'mobile',
            'contact_address' => 'address',
            'license_no' => 'credit_code',
            'license_image' => 'license_logo',
        ];

        $payload = [];
        foreach ($map as $from => $to) {
            if (array_key_exists($from, $data)) {
                $payload[$to] = $data[$from];
            }
        }

        foreach (['name', 'linkman', 'mobile', 'address', 'company', 'credit_code', 'license_logo', 'permit_code', 'permit_logo', 'status', 'cate_type', 'cate_ids', 'emergency_linkman', 'emergency_mobile', 'username', 'code'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        return $payload;
    }

    private function countGoodsBySupplierIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('goods_price')
            ->select('supp_id', DB::raw('COUNT(*) as cnt'))
            ->whereIn('supp_id', $ids)
            ->groupBy('supp_id')
            ->pluck('cnt', 'supp_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function countSchoolsBySupplierIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('bidding_log')
            ->select('supp_id', DB::raw('COUNT(*) as cnt'))
            ->where('status', 1)
            ->whereIn('supp_id', $ids)
            ->groupBy('supp_id')
            ->pluck('cnt', 'supp_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }
}
