<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 供应商创建请求验证
 */
class SupplierCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_name' => 'required_without:name|string|max:100|unique:supplier,name',
            'name' => 'required_without:supplier_name|string|max:100|unique:supplier,name',
            'contact_name' => 'required_without:linkman|string|max:50',
            'linkman' => 'required_without:contact_name|string|max:50',
            'contact_phone' => 'required_without:mobile|string|max:20',
            'mobile' => 'required_without:contact_phone|string|max:20',
            'contact_address' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:200',
            'company' => 'nullable|string|max:100',
            'license_no' => 'nullable|string|max:50',
            'license_image' => 'nullable|string|max:500',
            'permit_code' => 'nullable|string|max:50',
            'permit_logo' => 'nullable|string|max:500',
            'status' => ['nullable', Rule::in([0, 1])],
            'cate_type' => ['nullable', Rule::in([1, 2])],
            'cate_ids' => 'nullable|string',
            'username' => 'nullable|string|max:50|unique:supplier,username',
            'code' => 'nullable|string|max:20',
            'emergency_linkman' => 'nullable|string|max:50',
            'emergency_mobile' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_name.required_without' => '供应商名称不能为空',
            'name.required_without' => '供应商名称不能为空',
            'supplier_name.unique' => '供应商名称已存在',
            'name.unique' => '供应商名称已存在',
            'contact_name.required_without' => '联系人不能为空',
            'linkman.required_without' => '联系人不能为空',
            'contact_phone.required_without' => '联系电话不能为空',
            'mobile.required_without' => '联系电话不能为空',
        ];
    }
}
