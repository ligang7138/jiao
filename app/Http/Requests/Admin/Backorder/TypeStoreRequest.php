<?php

namespace App\Http\Requests\Admin\Backorder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 退货原因类型新增/编辑验证请求
 */
class TypeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id') ?? 0;

        return [
            'name' => 'required|string|max:50|unique:backorder_type,name,' . $id,
            'home' => 'nullable|integer|in:0,1',
            'sort' => 'nullable|integer|min:0|max:9999',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '退货原因名称不能为空',
            'name.max' => '退货原因名称不能超过50个字符',
            'name.unique' => '退货原因名称已存在',
            'home.in' => '前台显示状态值无效',
            'sort.min' => '排序值不能小于0',
            'sort.max' => '排序值不能超过9999',
            'status.in' => '状态值无效',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '退货原因名称',
            'home' => '前台显示',
            'sort' => '排序',
            'status' => '状态',
        ];
    }
}
