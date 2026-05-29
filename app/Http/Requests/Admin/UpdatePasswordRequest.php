<?php

namespace App\Http\Requests\Admin;

/**
 * 修改密码请求验证
 */
class UpdatePasswordRequest extends BaseFormRequest
{
    /**
     * 验证规则
     */
    public function rules(): array
    {
        return [
            'old_password' => 'required|string|min:6|max:20',
            'new_password' => [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^[\x21-\x9e]+$/u', // 英文字母、数字、下划线、半角符号
                'different:old_password',
            ],
            'new_password_confirmation' => 'required|same:new_password',
        ];
    }

    /**
     * 验证消息
     */
    public function messages(): array
    {
        return [
            'old_password.required' => '请输入原密码',
            'old_password.min' => '原密码至少6个字符',
            'old_password.max' => '原密码最多20个字符',
            'new_password.required' => '请输入新密码',
            'new_password.min' => '新密码至少6个字符',
            'new_password.max' => '新密码最多20个字符',
            'new_password.regex' => '密码只能包含英文字母、数字、下划线和半角符号',
            'new_password.different' => '新密码不能与原密码相同',
            'new_password_confirmation.required' => '请确认新密码',
            'new_password_confirmation.same' => '两次输入的密码不一致',
        ];
    }
}
