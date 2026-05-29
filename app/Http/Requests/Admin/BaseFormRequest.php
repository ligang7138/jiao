<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Helpers\ResponseHelper;
use App\Constants\ErrorCode;

/**
 * 表单请求基类
 */
abstract class BaseFormRequest extends FormRequest
{
    /**
     * 验证失败处理
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ResponseHelper::error(
                ErrorCode::VALIDATION_ERROR,
                '参数验证失败',
                $validator->errors()
            )
        );
    }

    /**
     * 授权失败处理
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            ResponseHelper::error(ErrorCode::PERMISSION_DENIED, '无权限执行此操作')
        );
    }
}
