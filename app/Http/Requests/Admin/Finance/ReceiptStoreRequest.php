<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 对账单创建验证请求
 */
class ReceiptStoreRequest extends FormRequest
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
            'canteen_id' => [
                'required',
                'integer',
                Rule::exists('school_canteen', 'id')->whereNull('deleted_at'),
            ],
            'supp_id' => [
                'required',
                'integer',
                Rule::exists('supplier', 'id')->whereNull('deleted_at'),
            ],
            'order_ids' => 'nullable|array',
            'order_ids.*' => [
                'integer',
                Rule::exists('orders', 'id')->where('status', 4)->whereNull('deleted_at'),
            ],
            'remark' => 'nullable|string|max:500',
        ];
    }

    /**
     * 获取验证错误的自定义属性名称
     */
    public function attributes(): array
    {
        return [
            'canteen_id' => '食堂',
            'supp_id' => '供应商',
            'order_ids' => '订单',
            'order_ids.*' => '订单ID',
            'remark' => '备注',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'canteen_id.required' => '请选择食堂',
            'canteen_id.exists' => '所选食堂不存在',
            'supp_id.required' => '请选择供应商',
            'supp_id.exists' => '所选供应商不存在',
            'order_ids.array' => '订单格式错误',
            'order_ids.*.exists' => '订单不存在或状态不正确',
        ];
    }
}
