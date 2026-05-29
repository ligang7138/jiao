<?php

namespace App\Http\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 商品更新请求验证
 */
class GoodsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'goods_name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|integer|exists:category,id',
            'unit' => 'sometimes|required|string|max:50',
            'spec' => 'nullable|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'supplier_id' => 'sometimes|required|integer|exists:supplier,id',
            'status' => ['sometimes', 'required', Rule::in([0, 1, 2])],
            'image' => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
            'sort' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'goods_name.required' => '商品名称不能为空',
            'goods_name.max' => '商品名称不能超过255个字符',
            'category_id.required' => '请选择分类',
            'category_id.exists' => '分类不存在',
            'unit.required' => '单位不能为空',
            'price.required' => '价格不能为空',
            'price.numeric' => '价格格式错误',
            'price.min' => '价格不能小于0',
            'supplier_id.required' => '请选择供应商',
            'supplier_id.exists' => '供应商不存在',
            'status.required' => '请选择状态',
            'status.in' => '状态值无效',
            'image_file.image' => '请上传图片文件',
            'image_file.max' => '图片不能超过2MB',
        ];
    }
}