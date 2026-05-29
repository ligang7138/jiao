<?php

namespace App\Http\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 商品更新请求验证（对齐旧 goods.edit 可编辑字段）
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
            'remark' => 'nullable|string',
            'place' => 'nullable|string|max:100',
            'expire_date' => 'nullable|string|max:50',
            'goods_type' => 'nullable|integer|in:0,1',
            'goods_channel' => 'nullable|integer|in:0,1',
            'image_list' => 'nullable|array|min:1',
            'image_list.*' => 'string',
            'detail_image_list' => 'nullable|array',
            'detail_image_list.*' => 'string',
        ];
    }

    public function messages(): array
    {
        return [
            'image_list.min' => '请上传商品图片！',
        ];
    }
}
