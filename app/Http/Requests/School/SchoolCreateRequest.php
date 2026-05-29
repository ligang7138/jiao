<?php

namespace App\Http\Requests\School;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 学校创建请求验证
 */
class SchoolCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_name' => 'required|string|max:255',
            'school_code' => 'nullable|string|max:50|unique:school,school_code',
            'contact_name' => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|integer|in:0,1',
            'credit_code' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'school_name.required' => '请输入学校名称',
            'school_name.max' => '学校名称不能超过255个字符',
            'school_code.unique' => '学校编码已存在',
            'contact_name.required' => '请输入联系人',
            'contact_phone.required' => '请输入联系电话',
        ];
    }
}
