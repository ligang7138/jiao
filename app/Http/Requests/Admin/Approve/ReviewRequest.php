<?php

namespace App\Http\Requests\Admin\Approve;

use App\Http\Requests\Admin\BaseFormRequest;

/**
 * 审阅请求验证
 */
class ReviewRequest extends BaseFormRequest
{
    /**
     * 获取验证规则
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
        ];
    }

    /**
     * 获取验证错误的自定义属性名称
     */
    public function attributes(): array
    {
        return [
            'id' => '记录ID',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'id.required' => '记录ID不能为空',
            'id.integer' => '记录ID必须为整数',
            'id.min' => '记录ID必须大于0',
        ];
    }
}
