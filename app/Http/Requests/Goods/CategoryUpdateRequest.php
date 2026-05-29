<?php

namespace App\Http\Requests\Goods;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 分类更新请求验证
 */
class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('id');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('category', 'name')->ignore($categoryId),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('category', 'id')->where(function ($query) use ($categoryId) {
                    $query->where('id', '!=', $categoryId); // 不能选择自己作为父级
                }),
            ],
            'sort' => 'nullable|integer|min:0|max:9999',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => '分类名称不能为空',
            'name.max' => '分类名称不能超过100个字符',
            'name.unique' => '分类名称已存在',
            'parent_id.exists' => '父级分类不存在或不能选择自己',
            'sort.integer' => '排序必须是整数',
            'sort.min' => '排序不能小于0',
            'sort.max' => '排序不能超过9999',
        ];
    }
}