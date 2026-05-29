<?php

namespace App\Http\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 分类创建请求验证（对齐旧 category.add）
 */
class CategoryCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50',
            'pid' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|integer|min:0',
            'sort' => 'nullable|integer|min:0|max:9999',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '分类名称不能为空',
            'name.max' => '分类名称不能超过50个字符',
        ];
    }
}
