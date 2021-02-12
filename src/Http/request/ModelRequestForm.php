<?php

namespace Smart\Gii\Http\request;

use Illuminate\Foundation\Http\FormRequest;
use Smart\Common\Traits\Request\Scenario;
use Smart\Gii\Http\Rules\ClassExist;

class ModelRequestForm extends FormRequest
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
            'connection' => ['required'],
            'table' => ['required'],
            'model' => ['required'],
            'baseModel' => ['required', new ClassExist()],
        ];

        $rules['generate'] = array_merge($rules['preview'], [
            'classes' => ['required'],
        ]);

        $rules['fixer'] = [
            'model' => ['required'],
        ];

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
            'connection' => '数据库连接',
            'table' => '表名',
            'model' => 'Model类',
            'baseModel' => 'Model基类',
            'classes' => '代码文件',
        ];
    }
}
