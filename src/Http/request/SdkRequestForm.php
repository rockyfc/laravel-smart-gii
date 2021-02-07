<?php

namespace Smart\Gii\Http\request;

use Illuminate\Foundation\Http\FormRequest;
use Smart\Common\Traits\Request\Scenario;

class SdkRequestForm extends FormRequest
{
    use Scenario;

    /**
     * 判断用户是否有权限进行此请求。
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = [];
        $rules['preview'] = [
            'namespace' => ['required'],
        ];

        $rules['generate'] = array_merge($rules['preview'], [
            'classes' => ['required'],
        ]);

        return $rules[$this->scenario];
    }

    /**
     * 获取验证错误的自定义属性。
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'form' => 'Form Request 类',
            'model' => 'Model类',
            'classes' => '代码文件',
        ];
    }
}
