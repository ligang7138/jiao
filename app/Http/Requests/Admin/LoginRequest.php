<?php

namespace App\Http\Requests\Admin;

/**
 * 登录请求验证
 */
class LoginRequest extends BaseFormRequest
{
    /**
     * 验证规则
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|min:2|max:50',
            'password' => 'required|string|min:5|max:20',
            'captcha_key' => 'nullable|string',
            'captcha_code' => 'nullable|string|size:4',
        ];
    }

    /**
     * 验证消息
     */
    public function messages(): array
    {
        return [
            'username.required' => '请输入用户名',
            'username.min' => '用户名至少2个字符',
            'username.max' => '用户名最多50个字符',
            'password.required' => '请输入密码',
            'password.min' => '密码至少5个字符',
            'password.max' => '密码最多20个字符',
            'captcha_code.size' => '验证码为4位',
        ];
    }
}
