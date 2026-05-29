<?php

namespace App\Http\Requests\Admin\Backorder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 退货单审核验证请求
 */
class AuditRequest extends FormRequest
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
            'solution' => 'nullable|string|max:500',
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
            'solution.max' => '解决方案不能超过500个字符',
        ];
    }
}
