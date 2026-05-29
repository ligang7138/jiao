<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 供应商更新请求验证
 */
class SupplierUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplierId = $this->route('id');

        return [
            'supplier_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('supplier', 'name')->ignore($supplierId),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('supplier', 'name')->ignore($supplierId),
            ],
            'contact_name' => 'sometimes|required|string|max:50',
            'linkman' => 'sometimes|required|string|max:50',
            'contact_phone' => 'sometimes|required|string|max:20',
            'mobile' => 'sometimes|required|string|max:20',
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
            'emergency_linkman' => 'nullable|string|max:50',
            'emergency_mobile' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_name.required' => '供应商名称不能为空',
            'supplier_name.unique' => '供应商名称已存在',
            'name.unique' => '供应商名称已存在',
            'contact_name.required' => '联系人不能为空',
            'contact_phone.required' => '联系电话不能为空',
        ];
    }
}
