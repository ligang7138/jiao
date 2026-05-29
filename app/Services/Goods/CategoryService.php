<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use Illuminate\Support\Facades\DB;

/**
 * 分类服务层
 */
class CategoryService
{
    /**
     * 获取分类树
     */
    public function getTree(): array
    {
        return Category::getTree();
    }

    /**
     * 获取分类列表（平铺）
     */
    public function getList(array $params): array
    {
        $query = Category::with('parent');

        // 关键词搜索
        if ($keyword = $params['keyword'] ?? null) {
            $query->where('name', 'like', "%{$keyword}%");
        }

        // 状态筛选
        if ($isActive = $params['is_active'] ?? null) {
            $query->where('is_active', $isActive);
        }

        $list = $query->orderBy('sort')->get();

        return $list->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'parent_id' => $item->parent_id,
                'parent_name' => $item->parent?->name,
                'sort' => $item->sort,
                'icon' => $item->icon,
                'is_active' => $item->is_active,
                'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * 创建分类
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * 更新分类
     */
    public function update(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);
        $category->update($data);

        return $category->fresh();
    }

    /**
     * 删除分类
     */
    public function delete(int $id): bool
    {
        $category = Category::findOrFail($id);

        // 检查是否有子分类
        if ($category->children()->count() > 0) {
            throw new \Exception('该分类下存在子分类，无法删除');
        }

        // 检查是否有商品
        if ($category->goods()->count() > 0) {
            throw new \Exception('该分类下存在商品，无法删除');
        }

        return $category->delete();
    }

    /**
     * 获取分类详情
     */
    public function getDetail(int $id): array
    {
        $category = Category::with('parent')->findOrFail($id);

        return [
            'id' => $category->id,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'parent_name' => $category->parent?->name,
            'sort' => $category->sort,
            'icon' => $category->icon,
            'description' => $category->description,
            'is_active' => $category->is_active,
            'created_at' => $category->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 获取所有顶级分类
     */
    public function getTopCategories(): array
    {
        $categories = Category::where('parent_id', 0)
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        return $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon,
            ];
        })->toArray();
    }

    /**
     * 获取分类下的子分类
     */
    public function getChildren(int $parentId): array
    {
        $categories = Category::where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('sort')
            ->get();

        return $categories->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon,
            ];
        })->toArray();
    }
}