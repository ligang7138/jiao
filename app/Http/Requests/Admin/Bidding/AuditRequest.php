<?php

namespace App\Http\Requests\Admin\Bidding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Bidding\BiddingHistory;

/**
 * 审核合作申请验证规则
 */
class AuditRequest extends FormRequest
{
    /**
     * 判断用户是否有权限进行此请求
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 获取应用于请求的验证规则
     */
    public function rules(): array
    {
        return [
            'audit_status' => [
                'required',
                'integer',
                Rule::in([
                    BiddingHistory::AUDIT_STATUS_APPROVED,
                    BiddingHistory::AUDIT_STATUS_REJECTED,
                ]),
            ],
            'audit_remark' => 'nullable|string|max:500',
        ];
    }

    /**
     * 获取验证错误的自定义属性名称
     */
    public function attributes(): array
    {
        return [
            'audit_status' => '审核状态',
            'audit_remark' => '审核备注',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'audit_status.required' => '请选择审核状态',
            'audit_status.integer' => '审核状态格式错误',
            'audit_status.in' => '审核状态值无效',
            'audit_remark.max' => '审核备注不能超过500个字符',
        ];
    }
}
