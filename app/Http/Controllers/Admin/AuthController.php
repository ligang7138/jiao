<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;
use App\Helpers\AuthHelper;
use App\Constants\ErrorCode;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Support\LegacyMenuBuilder;

/**
 * 认证控制器
 */
class AuthController extends Controller
{
    /**
     * 用户登录
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        $user = User::query()->where('username', $credentials['username'])->first();
        if (!$user || !AuthHelper::verifyPassword(
            $credentials['password'],
            (string) $user->password,
            (string) ($user->salt ?? '')
        )) {
            return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '用户名或密码错误');
        }

        if ((int) $user->status !== 1) {
            return ResponseHelper::error(ErrorCode::FORBIDDEN, '账号已被禁用');
        }

        if (
            AuthHelper::supportsModernPasswordStorage()
            && !empty($user->salt)
            && AuthHelper::verifyLegacyPassword(
                $credentials['password'],
                (string) $user->password,
                (string) $user->salt
            )
        ) {
            AuthHelper::upgradePassword($user, $credentials['password']);
        }

        if (!$token = auth('jwt')->login($user)) {
            return ResponseHelper::error(ErrorCode::UNAUTHORIZED, '登录失败');
        }

        // 加载用户权限
        $permissions = $this->loadUserPermissions($user);

        $this->touchLoginMeta($user, $request->ip());

        return ResponseHelper::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('jwt')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'is_super' => $user->isSuper(),
                'permissions' => $permissions,
            ],
        ]);
    }

    /**
     * 用户登出
     */
    public function logout(Request $request)
    {
        auth('jwt')->logout();

        return ResponseHelper::success(null, '退出成功');
    }

    /**
     * 刷新 Token
     */
    public function refresh()
    {
        $token = auth('jwt')->refresh();

        return ResponseHelper::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('jwt')->factory()->getTTL() * 60,
        ]);
    }

    /**
     * 获取当前用户信息
     */
    public function me()
    {
        $user = auth('jwt')->user();
        $permissions = $this->loadUserPermissions($user);

        return ResponseHelper::success([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'avatar' => $user->avatar ?? '',
            'email' => $user->email ?? '',
            'mobile' => $user->mobile ?? '',
            'department_id' => $user->department_id,
            'department_name' => $user->department?->name,
            'posts' => $this->resolvePostNames($user),
            'is_super' => $user->isSuper(),
            'permissions' => $permissions,
            'privilege' => $permissions,
            'modules' => LegacyMenuBuilder::resolveAllowedModules(
                LegacyMenuBuilder::collectMenuIdsForUser($user)
            ),
        ]);
    }

    private function touchLoginMeta($user, string $ip): void
    {
        $dirty = false;

        if (\Illuminate\Support\Facades\Schema::hasColumn($user->getTable(), 'last_login_time')) {
            $user->last_login_time = time();
            $dirty = true;
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn($user->getTable(), 'last_login_ip')) {
            $user->last_login_ip = $ip;
            $dirty = true;
        }

        if ($dirty) {
            $user->save();
        }
    }

    private function resolvePostNames($user): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('admin_user_posts')) {
            $user->loadMissing('posts');

            if ($user->posts->isNotEmpty()) {
                return $user->posts->pluck('name')->filter()->values()->all();
            }
        }

        $postField = $user->post ?? '';
        if ($postField === '') {
            return [];
        }

        $postIds = array_values(array_filter(array_map('intval', explode(',', (string) $postField))));
        if (empty($postIds)) {
            return [];
        }

        $postTable = config('legacy.auth.post_table', 'post');

        return \Illuminate\Support\Facades\DB::table($postTable)
            ->whereIn('id', $postIds)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * 修改密码
     */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth('jwt')->user();
        $oldPassword = $request->input('old_password');
        $newPassword = $request->input('new_password');

        // 验证旧密码
        if (!AuthHelper::verifyPassword($oldPassword, $user->password, $user->salt ?? '')) {
            return ResponseHelper::error(ErrorCode::VALIDATION_ERROR, '原密码错误');
        }

        // 更新密码
        $user->password = AuthHelper::encryptPassword($newPassword);
        $user->salt = '';
        $user->save();

        return ResponseHelper::success(null, '密码修改成功');
    }

    /**
     * 更新个人信息
     */
    public function updateProfile(Request $request)
    {
        $user = auth('jwt')->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'avatar' => 'sometimes|string|max:500',
            'email' => 'sometimes|email|max:100',
            'mobile' => 'sometimes|string|max:20',
        ]);

        $user->fill($validated);
        $user->save();

        return ResponseHelper::success(null, '更新成功');
    }

    /**
     * 获取验证码
     */
    public function captcha()
    {
        // 生成验证码逻辑
        $code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $key = 'captcha:' . uniqid();

        // 存储验证码到缓存（5分钟有效）
        cache()->put($key, $code, 300);

        return ResponseHelper::success([
            'key' => $key,
            // 实际项目中应该返回图片验证码
            // 'image' => base64_encode($image),
        ]);
    }

    /**
     * 忘记密码
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email',
        ]);

        // 实际项目中应该发送重置密码邮件
        return ResponseHelper::success(null, '如果邮箱正确，您将收到重置密码的邮件');
    }

    /**
     * 加载用户权限列表
     */
    private function loadUserPermissions($user): array
    {
        if ($user->isSuper()) {
            return ['*'];
        }

        return LegacyMenuBuilder::resolvePrivilegePaths($user);
    }
}
