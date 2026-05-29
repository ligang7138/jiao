<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Models\Goods\Goods;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 分类服务层（对齐旧 admin/category 业务逻辑）
 */
class CategoryService
{
    public function getTree(): array
    {
        return Category::getTree();
    }

    public function getList(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($params['page_size'] ?? 20)));

        $query = Category::query();

        if (($pid = $params['pid'] ?? $params['parent_id'] ?? '') !== '' && $pid !== null) {
            $query->where('pid', (int) $pid);
        }

        if ($name = trim((string) ($params['name'] ?? $params['keyword'] ?? ''))) {
            $query->where('name', 'like', "%{$name}%");
        }

        if (($status = $params['status'] ?? '') !== '' && $status !== null) {
            $query->where('status', (int) $status);
        }

        $total = $query->count();
        $list = $query->orderBy('pid')->orderBy('sort')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->map(fn ($item) => $this->formatCategory($item))
            ->values()
            ->all();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'page_size' => $pageSize,
        ];
    }

    public function create(array $data): Category
    {
        $pid = (int) ($data['pid'] ?? $data['parent_id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $sort = (int) ($data['sort'] ?? 0);

        if (Category::where('pid', $pid)->where('name', $name)->exists()) {
            throw new \InvalidArgumentException('不允许添加同名分类');
        }

        $user = Auth::user();

        return Category::create([
            'name' => $name,
            'pid' => $pid,
            'sort' => $sort,
            'status' => (int) ($data['status'] ?? 1),
            'add_user' => $user?->name ?? '',
            'add_time' => time(),
        ]);
    }

    public function update(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $pid = (int) ($data['pid'] ?? $data['parent_id'] ?? $category->pid);
        $name = trim((string) ($data['name'] ?? $category->name));
        $sort = (int) ($data['sort'] ?? $category->sort);
        $status = (int) ($data['status'] ?? $category->status);
        $oldName = $category->name;

        if (Category::where('pid', $pid)->where('name', $name)->where('id', '!=', $id)->exists()) {
            throw new \InvalidArgumentException('不允许添加同名分类');
        }

        $user = Auth::user();

        return DB::transaction(function () use ($category, $pid, $name, $sort, $status, $oldName, $data, $user) {
            $category->update([
                'name' => $name,
                'pid' => $pid,
                'sort' => $sort,
                'status' => $status,
                'allow_supplement_report_after_send' => (int) ($data['allow_supplement_report_after_send'] ?? $category->allow_supplement_report_after_send ?? 0),
                'update_user' => $user?->name ?? '',
                'update_time' => time(),
            ]);

            if ($oldName !== $name) {
                if ($pid === 0) {
                    Goods::where('cate_id', $category->id)->update(['cate_name' => $name]);
                } else {
                    Goods::where('scate_id', $category->id)->update(['scate_name' => $name]);
                }
            }

            return $category->fresh();
        });
    }

    public function delete(int $id): bool
    {
        $category = Category::findOrFail($id);

        if ($category->children()->count() > 0) {
            throw new \InvalidArgumentException('该分类下存在子分类，无法删除');
        }

        if ($category->goods()->count() > 0) {
            throw new \InvalidArgumentException('该分类下存在商品，无法删除');
        }

        return (bool) $category->delete();
    }

    public function getDetail(int $id): array
    {
        return $this->formatCategory(Category::findOrFail($id));
    }

    public function getTopCategories(): array
    {
        return Category::where('pid', 0)
            ->where('status', 1)
            ->orderBy('sort')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'logo' => $item->logo,
                'float_rate_cap' => $item->float_rate_cap,
            ])
            ->all();
    }

    public function getChildren(int $parentId): array
    {
        return Category::where('pid', $parentId)
            ->where('status', 1)
            ->orderBy('sort')
            ->get(['id', 'name', 'logo'])
            ->map(fn ($item) => $item->toArray())
            ->all();
    }

    public function setFloatRateCap(int $id, float $floatRateCapPercent): Category
    {
        if ($floatRateCapPercent < 0 || $floatRateCapPercent > 100) {
            throw new \InvalidArgumentException('请输入有效的浮动率上限(0-100)');
        }

        $category = Category::where('id', $id)->where('pid', 0)->firstOrFail();
        $floatRateCap = round($floatRateCapPercent / 100, 4);

        return DB::transaction(function () use ($category, $floatRateCap) {
            $category->update(['float_rate_cap' => $floatRateCap]);

            DB::table('discount_category')
                ->where('category_id', $category->id)
                ->where('float_rate', '>', $floatRateCap)
                ->update(['float_rate' => $floatRateCap]);

            return $category->fresh();
        });
    }

    private function formatCategory(Category $item): array
    {
        $pname = '';
        if ($item->pid > 0) {
            $pname = Category::where('id', $item->pid)->value('name') ?? '';
        }

        return [
            'id' => $item->id,
            'name' => $item->name,
            'pid' => (int) $item->pid,
            'parent_id' => (int) $item->pid,
            'pname' => $pname,
            'sort' => (int) $item->sort,
            'status' => (int) $item->status,
            'is_active' => (int) $item->status === 1,
            'logo' => $item->logo,
            'float_rate_cap' => $item->float_rate_cap,
            'allow_supplement_report_after_send' => (int) ($item->allow_supplement_report_after_send ?? 0),
            'add_user' => $item->add_user ?? '',
            'add_time' => (int) ($item->add_time ?? 0),
            'update_user' => $item->update_user ?? '',
            'update_time' => (int) ($item->update_time ?? 0),
        ];
    }
}
