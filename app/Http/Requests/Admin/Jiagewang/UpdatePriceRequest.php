<?php

namespace App\Http\Requests\Admin\Jiagewang;

use App\Http\Requests\Admin\BaseFormRequest;

/**
 * 指导价编辑请求验证
 */
class UpdatePriceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|min:0',
            'goods_sn' => 'required|string|max:50',
            'goods_id' => 'required|integer|exists:goods,id',
            'price' => 'required|numeric|min:0.01|max:999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'goods_sn.required' => '商品编码不能为空',
            'goods_sn.max' => '商品编码不能超过50个字符',
            'goods_id.required' => '商品ID不能为空',
            'goods_id.exists' => '商品不存在',
            'price.required' => '指导价不能为空',
            'price.numeric' => '指导价格式错误',
            'price.min' => '指导价必须大于0',
            'price.max' => '指导价不能超过999999.99',
        ];
    }
}
