<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goods\CategoryCreateRequest;
use App\Http\Requests\Goods\CategoryUpdateRequest;
use App\Services\Goods\CategoryService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

/**
 * 分类管理控制器
 */
class CategoryController extends Controller
{
    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * 分类树形结构
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tree()
    {
        $tree = $this->categoryService->getTree();

        return ResponseHelper::success($tree);
    }

    /**
     * 分类列表（平铺）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $params = $request->only(['keyword', 'name', 'pid', 'parent_id', 'status', 'page', 'page_size']);

        $list = $this->categoryService->getList($params);

        return ResponseHelper::success($list);
    }

    /**
     * 创建分类
     *
     * @param CategoryCreateRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryCreateRequest $request)
    {
        $data = $request->validated();

        // 默认父级为0（顶级分类）
        if (!isset($data['pid']) && !isset($data['parent_id'])) {
            $data['pid'] = 0;
        } elseif (!isset($data['pid'])) {
            $data['pid'] = (int) ($data['parent_id'] ?? 0);
        }

        try {
            $category = $this->categoryService->create($data);

            return ResponseHelper::success([
                'id' => $category->id,
                'name' => $category->name,
            ], '新增成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40009, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error('新增失败', 40009);
        }
    }

    /**
     * 分类详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $detail = $this->categoryService->getDetail($id);

            return ResponseHelper::success($detail);
        } catch (\Exception $e) {
            return ResponseHelper::error('分类不存在');
        }
    }

    /**
     * 更新分类
     *
     * @param CategoryUpdateRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryUpdateRequest $request, int $id)
    {
        $data = $request->validated();

        try {
            $category = $this->categoryService->update($id, $data);

            return ResponseHelper::success([
                'id' => $category->id,
            ], '编辑成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40009, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error('编辑失败' . $e->getMessage(), 40009);
        }
    }

    /**
     * 删除分类
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->categoryService->delete($id);

            return ResponseHelper::success([], '分类删除成功');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    /**
     * 获取顶级分类
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopCategories()
    {
        $categories = $this->categoryService->getTopCategories();

        return ResponseHelper::success($categories);
    }

    /**
     * 获取子分类
     *
     * @param int $parentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChildren(int $parentId)
    {
        $categories = $this->categoryService->getChildren($parentId);

        return ResponseHelper::success($categories);
    }

    /**
     * 设置分类状态
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setStatus(Request $request, int $id)
    {
        $status = $request->input('status');

        if (!in_array($status, [0, 1])) {
            return ResponseHelper::error('状态值无效');
        }

        try {
            $category = $this->categoryService->update($id, ['status' => $status]);

            return ResponseHelper::success([
                'id' => $category->id,
                'status' => $category->status,
            ], '状态更新成功');
        } catch (\Exception $e) {
            return ResponseHelper::error('状态更新失败: ' . $e->getMessage());
        }
    }

    /**
     * 设置浮动率上限
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setFloatRateCap(Request $request, int $id)
    {
        $floatRateCap = $request->input('float_rate_cap');

        if (!is_numeric($floatRateCap) || $floatRateCap < 0 || $floatRateCap > 100) {
            return ResponseHelper::error(40001, '请输入有效的浮动率上限(0-100)');
        }

        try {
            $category = $this->categoryService->setFloatRateCap($id, (float) $floatRateCap);

            return ResponseHelper::success([
                'id' => $category->id,
                'float_rate_cap' => $category->float_rate_cap,
            ], '设置成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Exception $e) {
            return ResponseHelper::error(40009, '设置失败');
        }
    }
}