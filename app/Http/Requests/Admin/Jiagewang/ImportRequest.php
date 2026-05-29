<?php

namespace App\Http\Requests\Admin\Jiagewang;

use App\Http\Requests\Admin\BaseFormRequest;

/**
 * 指导价导入请求验证
 */
class ImportRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,xls,xlsx|max:10240', // 最大10MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => '请选择要导入的文件',
            'file.file' => '请上传有效的文件',
            'file.mimes' => '文件格式不支持，仅支持 csv, xls, xlsx 格式',
            'file.max' => '文件大小不能超过10MB',
        ];
    }
}
