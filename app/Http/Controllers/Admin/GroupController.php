<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ErrorCode;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Group\Group;
use App\Services\Group\GroupService;
use Illuminate\Http\Request;

/**
 * 分组管理控制器
 */
class GroupController extends Controller
{
    public function __construct(
        protected GroupService $groupService
    ) {}

    public function index(Request $request)
    {
        $params = $request->only(['name', 'pid', 'id', 'status', 'page', 'page_size']);

        return ResponseHelper::success($this->groupService->getList($params));
    }

    public function options(Request $request)
    {
        $pid = (int) $request->input('pid', 0);

        $groups = Group::query()
            ->where('pid', $pid)
            ->where('status', Group::STATUS_ENABLED)
            ->orderBy('id')
            ->get(['id', 'name']);

        return ResponseHelper::success($groups);
    }

    public function show(int $id)
    {
        try {
            return ResponseHelper::success($this->groupService->getDetail($id));
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'pid' => 'nullable|integer|min:0',
            'code' => 'nullable|string|max:50',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $group = $this->groupService->create($data);

            return ResponseHelper::success(['id' => $group->id], '新增成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(400, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '新增失败');
        }
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:50',
            'pid' => 'nullable|integer|min:0',
            'code' => 'nullable|string|max:50',
            'status' => 'nullable|integer|in:0,1',
        ]);

        try {
            $group = $this->groupService->update($id, $data);

            return ResponseHelper::success(['id' => $group->id], '编辑成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '编辑失败');
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->groupService->delete($id);

            return ResponseHelper::success([], '删除成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        }
    }

    public function canteens(int $id)
    {
        try {
            return ResponseHelper::success($this->groupService->getCanteens($id));
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40002, $e->getMessage());
        }
    }

    public function addCanteen(Request $request, int $id)
    {
        $canteenId = (int) $request->input('canteen_id');

        try {
            $this->groupService->addCanteen($id, $canteenId);

            return ResponseHelper::success([], '提交成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '提交失败');
        }
    }

    public function removeCanteen(int $groupId, int $canteenId)
    {
        try {
            $this->groupService->removeCanteen($groupId, $canteenId);

            return ResponseHelper::success([], '删除成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '删除失败');
        }
    }

    public function setAudit(int $groupId, int $canteenId)
    {
        try {
            $this->groupService->setAudit($groupId, $canteenId);

            return ResponseHelper::success([], '设置审核员成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '设置审核员失败');
        }
    }

    public function removeAudit(int $groupId, int $canteenId)
    {
        try {
            $this->groupService->removeAudit($groupId, $canteenId);

            return ResponseHelper::success([], '移除审核员成功');
        } catch (\InvalidArgumentException $e) {
            return ResponseHelper::error(40001, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '移除审核员失败');
        }
    }
}
