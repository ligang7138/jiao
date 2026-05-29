<?php

namespace App\Http\Requests\Admin\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Finance\ReceivableAccount;

/**
 * 账单明细创建验证请求
 */
class AccountStoreRequest extends FormRequest
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
            'receipt_id' => [
                'required',
                'integer',
                Rule::exists('receivable_receipt', 'id')->whereNull('deleted_at'),
            ],
            'order_id' => [
                'nullable',
                'integer',
                Rule::exists('orders', 'id')->whereNull('deleted_at'),
            ],
            'type' => [
                'required',
                'integer',
                Rule::in([
                    ReceivableAccount::TYPE_DEBIT,
                    ReceivableAccount::TYPE_CREDIT,
                    ReceivableAccount::TYPE_INVOICE,
                    ReceivableAccount::TYPE_BILL,
                ]),
            ],
            'price' => 'required|numeric|min:0|max:99999999.99',
            'status' => 'nullable|integer|in:0,1',
            'remark' => 'nullable|string|max:500',
        ];
    }

    /**
     * 获取验证错误的自定义属性名称
     */
    public function attributes(): array
    {
        return [
            'receipt_id' => '对账单',
            'order_id' => '订单',
            'type' => '类型',
            'price' => '金额',
            'status' => '状态',
            'remark' => '备注',
        ];
    }

    /**
     * 获取验证错误的自定义消息
     */
    public function messages(): array
    {
        return [
            'receipt_id.required' => '请选择对账单',
            'receipt_id.exists' => '所选对账单不存在',
            'order_id.exists' => '所选订单不存在',
            'type.required' => '请选择类型',
            'type.in' => '类型值不正确',
            'price.required' => '请输入金额',
            'price.numeric' => '金额必须为数字',
            'price.min' => '金额不能小于0',
            'price.max' => '金额超出限制',
        ];
    }
}
