<?php

namespace App\Http\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 商品创建请求验证（对齐旧 goods.add）
 */
class GoodsCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'goods_name' => 'required|string|max:30',
            'cate_id' => 'required|integer',
            'scate_id' => 'required|integer',
            'spec' => 'required|string|max:30',
            'unit' => 'required|string|max:20',
            'attr' => ['required', Rule::in([1, 2, 3])],
            'level' => ['required', Rule::in([1, 2])],
            'goods_type' => 'nullable|integer|in:0,1',
            'goods_channel' => 'nullable|integer|in:0,1',
            'image_list' => 'required|array|min:1',
            'image_list.*' => 'string',
            'detail_image_list' => 'nullable|array',
            'detail_image_list.*' => 'string',
            'remark' => 'nullable|string',
            'brand' => 'nullable|string|max:50',
            'place' => 'nullable|string|max:100',
            'expire_date' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'goods_name.required' => '请输入商品名称',
            'cate_id.required' => '请选择一级分类',
            'scate_id.required' => '请选择二级分类',
            'spec.required' => '请输入规格',
            'unit.required' => '请选择单位',
            'attr.required' => '请选择商品属性',
            'level.required' => '请选择等级',
            'image_list.required' => '请上传商品图片！',
            'image_list.min' => '请上传商品图片！',
        ];
    }
}
