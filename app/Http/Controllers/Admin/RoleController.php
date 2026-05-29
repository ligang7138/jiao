<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Admin\SystemManagementService;
use Illuminate\Http\Request;
use RuntimeException;

class RoleController extends Controller
{
    public function __construct(private readonly SystemManagementService $service)
    {
    }

    public function index(Request $request)
    {
        return ResponseHelper::success($this->service->roleList($request->all()));
    }

    public function options(Request $request)
    {
        return ResponseHelper::success($this->service->roleOptions($request->all()));
    }

    public function show($id)
    {
        $role = $this->service->roleDetail((int) $id);
        if (!$role) {
            return ResponseHelper::error(40001, '记录不存在');
        }

        return ResponseHelper::success($role);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'department_id' => 'required|integer',
            'remark' => 'nullable|string|max:100',
            'status' => 'required|integer|in:0,1',
        ], [
            'name.required' => '请输入岗位名称',
            'department_id.required' => '请选择部门',
        ]);

        try {
            $id = $this->service->createRole($validated, $request->user());
            return ResponseHelper::success(['id' => $id], '新增成功');
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40001, $e->getMessage());
        } catch (\Throwable) {
            return ResponseHelper::error(40001, '新增失败');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'department_id' => 'required|integer',
            'remark' => 'nullable|string|max:100',
            'status' => 'required|integer|in:0,1',
        ], [
            'name.required' => '请输入岗位名称',
            'department_id.required' => '请选择部门',
        ]);

        try {
            $this->service->updateRole((int) $id, $validated);
            return ResponseHelper::success(null, '编辑成功');
        } catch (\Throwable) {
            return ResponseHelper::error(40001, '编辑失败');
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        try {
            $this->service->updateRoleStatus((int) $id, (int) $validated['status']);
            return ResponseHelper::success(null, '设置成功');
        } catch (\Throwable) {
            return ResponseHelper::error(40001, '设置失败');
        }
    }

    public function privilege($id)
    {
        try {
            return ResponseHelper::success($this->service->rolePermission((int) $id));
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40001, $e->getMessage());
        }
    }

    public function updatePrivilege(Request $request, $id)
    {
        $data = $request->input('data', $request->input('permission_ids', []));
        if (is_string($data)) {
            $data = trim($data);
        }
        $this->service->updateRolePermission((int) $id, $data);

        return ResponseHelper::success(null, '设置成功');
    }
}
