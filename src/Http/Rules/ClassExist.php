<?php

namespace Smart\Gii\Http\Rules;

use Illuminate\Contracts\Validation\Rule;

class ClassExist implements Rule
{
    /**
     * 确定验证规则是否通过。
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return class_exists($value);
    }

    /**
     * 获取验证错误消息。
     *
     * @return string
     */
    public function message()
    {
        return ':attribute 不存在。';
    }
}
