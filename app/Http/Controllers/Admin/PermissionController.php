<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Admin\SystemManagementService;
use Illuminate\Http\Request;
use RuntimeException;

class PermissionController extends Controller
{
    public function __construct(private readonly SystemManagementService $service)
    {
    }

    public function index(Request $request)
    {
        return ResponseHelper::success($this->service->permissionList($request->all()));
    }

    public function tree(Request $request)
    {
        return ResponseHelper::success($this->service->permissionTree((bool) $request->boolean('active_only')));
    }

    public function modules()
    {
        return ResponseHelper::success($this->service->permissionModules());
    }

    public function controls(Request $request)
    {
        return ResponseHelper::success($this->service->permissionControls((int) $request->input('id')));
    }

    public function show($id)
    {
        $permission = $this->service->permissionDetail((int) $id);
        if (!$permission) {
            return ResponseHelper::error(40001, '记录不存在');
        }

        return ResponseHelper::success($permission);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePermission($request);

        try {
            $id = $this->service->createPermission($validated);
            return ResponseHelper::success(['id' => $id], '新增成功');
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40001, '新增失败:' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'privilege' => 'required|string|max:50',
            'path' => 'required|string|max:100',
            'status' => 'required|integer|in:0,1',
        ], [
            'privilege.required' => '请输入功能名称',
            'path.required' => '请输入权限路径',
        ]);

        try {
            $this->service->updatePermission((int) $id, $validated);
            return ResponseHelper::success(null, '编辑成功');
        } catch (\Throwable $e) {
            return ResponseHelper::error(40001, '编辑失败:' . $e->getMessage());
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        try {
            $this->service->updatePermissionStatus((int) $id, (int) $validated['status']);
            return ResponseHelper::success(null, '设置成功');
        } catch (\Throwable $e) {
            return ResponseHelper::error(40001, '设置失败:' . $e->getMessage());
        }
    }

    private function validatePermission(Request $request): array
    {
        $module = (string) $request->input('module', '');
        $func = (string) $request->input('func', '');

        $rules = [
            'module' => 'nullable',
            'func' => 'nullable',
            'privilege' => 'required|string|max:50',
            'path' => 'nullable|string|max:100',
            'status' => 'required|integer|in:0,1',
        ];
        if ($module !== '' || $func !== '') {
            $rules['path'] = 'required|string|max:100';
        }

        return $request->validate($rules, [
            'privilege.required' => $module === '' && $func === '' ? '请输入模块名称' : '请输入功能名称',
            'path.required' => '请输入权限路径',
        ]);
    }
}
