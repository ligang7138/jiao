<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\Admin\SystemManagementService;
use Illuminate\Http\Request;
use RuntimeException;

class UserController extends Controller
{
    public function __construct(private readonly SystemManagementService $service)
    {
    }

    public function index(Request $request)
    {
        return ResponseHelper::success($this->service->userList($request->all()));
    }

    public function options(Request $request)
    {
        $data = $this->service->userList([
            ...$request->all(),
            'page' => 1,
            'page_size' => $request->input('page_size', 1000),
            'status' => $request->input('status', 1),
        ]);

        return ResponseHelper::success($data['list']);
    }

    public function show($id)
    {
        $user = $this->service->userDetail((int) $id);
        if (!$user) {
            return ResponseHelper::error(40001, '记录不存在');
        }

        return ResponseHelper::success($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'name' => 'required|string|max:50',
            'department_id' => 'required|integer',
            'mobile' => ['required', 'regex:/^1\d{10}$/'],
            'remark' => 'nullable|string|max:255',
            'status' => 'required|integer|in:0,1',
        ], [
            'username.required' => '请输入登陆账号',
            'name.required' => '请输入用户名称',
            'department_id.required' => '请选择部门',
            'mobile.required' => '请输入正确的手机号',
            'mobile.regex' => '请输入正确的手机号',
        ]);

        try {
            $id = $this->service->createUser($validated);
            return ResponseHelper::success(['id' => $id], '添加成功');
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40009, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '添加失败' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'department_id' => 'required|integer',
            'mobile' => ['required', 'regex:/^1\d{10}$/'],
            'remark' => 'nullable|string|max:255',
        ], [
            'name.required' => '请输入用户名称',
            'department_id.required' => '请选择部门',
            'mobile.required' => '请输入正确的手机号',
            'mobile.regex' => '请输入正确的手机号',
        ]);

        try {
            $this->service->updateUser((int) $id, $validated);
            return ResponseHelper::success(null, '修改成功');
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '修改失败' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        return ResponseHelper::success(null, '删除成功');
    }

    public function changeStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:0,1',
        ]);

        try {
            $this->service->updateUserStatus((int) $id, (int) $validated['status']);
            return ResponseHelper::success(null, '设置成功');
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40009, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '设置失败' . $e->getMessage());
        }
    }

    public function resetPassword(Request $request, $id)
    {
        try {
            $this->service->resetUserPassword((int) $id);
            return ResponseHelper::success(null, '设置成功');
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40009, $e->getMessage());
        } catch (\Throwable $e) {
            return ResponseHelper::error(40009, '设置失败' . $e->getMessage());
        }
    }

    public function batchDestroy(Request $request)
    {
        return ResponseHelper::success(null, '批量删除成功');
    }

    public function getPrivilege(Request $request, $id)
    {
        try {
            return ResponseHelper::success($this->service->userPrivilege((int) $id, $request->all()));
        } catch (RuntimeException $e) {
            return ResponseHelper::error($e->getCode() ?: 40001, $e->getMessage());
        }
    }

    public function updatePrivilege(Request $request, $id)
    {
        $post = $request->input('post', $request->input('post_ids', []));
        $this->service->updateUserPrivilege((int) $id, $post);

        return ResponseHelper::success(null, '设置成功');
    }
}
