<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 订单创建请求验证
 */
class OrderCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_id' => 'required|integer|exists:school,id',
            'canteen_id' => 'required|integer|exists:school_canteen,id',
            'supplier_id' => 'required|integer|exists:supplier,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'remark' => 'nullable|string|max:500',
            'goods' => 'required|array|min:1',
            'goods.*.goods_id' => 'nullable|integer',
            'goods.*.goods_name' => 'required|string|max:255',
            'goods.*.unit' => 'required|string|max:50',
            'goods.*.spec' => 'nullable|string|max:255',
            'goods.*.price' => 'required|numeric|min:0',
            'goods.*.quantity' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'school_id.required' => '请选择学校',
            'school_id.exists' => '学校不存在',
            'canteen_id.required' => '请选择食堂',
            'canteen_id.exists' => '食堂不存在',
            'supplier_id.required' => '请选择供应商',
            'supplier_id.exists' => '供应商不存在',
            'order_date.required' => '请选择订单日期',
            'order_date.date' => '订单日期格式不正确',
            'delivery_date.date' => '送货日期格式不正确',
            'delivery_date.after_or_equal' => '送货日期不能早于订单日期',
            'goods.required' => '请添加商品',
            'goods.array' => '商品数据格式不正确',
            'goods.min' => '至少添加一个商品',
            'goods.*.goods_name.required' => '商品名称不能为空',
            'goods.*.unit.required' => '商品单位不能为空',
            'goods.*.price.required' => '商品价格不能为空',
            'goods.*.price.min' => '商品价格不能小于0',
            'goods.*.quantity.required' => '商品数量不能为空',
            'goods.*.quantity.min' => '商品数量必须大于0',
        ];
    }
}
