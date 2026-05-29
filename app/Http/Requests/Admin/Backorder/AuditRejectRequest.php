<?php

namespace App\Http\Requests\Admin\Backorder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 审核拒绝验证请求
 */
class AuditRejectRequest extends FormRequest
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
        return [
            'reason' => 'required|string|max:500',
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
            'reason.required' => '拒绝原因不能为空',
            'reason.max' => '拒绝原因不能超过500个字符',
        ];
    }
}
