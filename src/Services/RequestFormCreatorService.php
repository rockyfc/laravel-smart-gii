<?php

namespace Smart\Gii\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Smart\Common\Helpers\Tools;
use Smart\Common\Sources\Attributes;
use Smart\Common\Sources\SmartModel;

/**
 * 生成一个RequestForm类文件内容
 *
 * @author Rocky<softfc@163.com>
 */
class RequestFormCreatorService
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * 要创建的类名称
     * @var string
     */
    protected $className;

    /**
     * 设置model类
     * @var string
     */
    protected $modelClass;

    /**
     * @var string
     */
    protected $stub;

    /**
     * RequestFormCreatorService constructor.
     * @param string $formClass
     * @param string $modelClass
     */
    public function __construct(string $formClass, string $modelClass)
    {
        $this->files = new Filesystem();
        $this->className = $formClass;
        $this->modelClass = $modelClass;
    }

    /**
     * 获取要创建的文件内容
     * @throws FileNotFoundException
     * @return mixed
     */
    public function getFileContent()
    {
        return $this->buildClass($this->className);
    }

    /**
     * @param $name
     * @throws FileNotFoundException
     * @return mixed
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModelClass($stub)
            ->replaceRules($stub)
            ->replaceStoreRules($stub)
            ->replaceUpdateRules($stub)
            ->replaceSorts($stub)
            ->replaceClass($stub, $name);
    }

    /**
     * @param $stub
     * @param $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            '{{ namespace }}',
            $this->getNamespace($name),
            $stub
        );

        return $this;
    }

    /**
     * @param $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * @return string
     */
    protected function rootNamespace()
    {
        return app()->getNamespace();
    }

    /**
     * 替换掉模板里面的"{{ modelClassName }}" 和"{{ modelClass }}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceModelClass(&$stub)
    {
        $modelClass = $this->modelClass;
        $modelClassName = str_replace($this->getNamespace($this->modelClass) . '\\', '', $this->modelClass);

        // 如果model名和request form的类名相同的话，稍做改变
        if ($this->isSameWithModel($modelClassName)) {
            $modelClassName = $modelClassName . 'Model';
            $modelClass = $modelClass . ' as ' . $modelClassName;
        }

        $stub = str_replace(['{{ modelClassName }}', '{{modelClassName}}'], $modelClassName, $stub);
        $stub = str_replace(['{{ modelClass }}', '{{modelClass}}'], $modelClass, $stub);

        return $this;
    }

    /**
     * @return Attributes|SmartModel|Model
     */
    protected function newModelInstance()
    {
        return new $this->modelClass();
    }

    /**
     * 替换模板中的{{ rules }}标签
     * @param $stub
     * @return $this
     */
    protected function replaceRules(&$stub)
    {
        $model = $this->newModelInstance();
        $rules = (array)Tools::removeRequiredForRules(
            $model->fillableAttributesRules()
        );

        // 首先过滤出以"id"结尾的字段
        $arr = array_filter($rules, function ($attribute) {
            return Str::endsWith($attribute, 'id');
        }, ARRAY_FILTER_USE_KEY);

        // 如果以"id"结尾的字段不足10个，则再挑选出name结尾的字段
        if (count($arr) < 10) {
            $arr = array_merge(
                $arr,
                array_filter($rules, function ($attribute) {
                    return Str::endsWith($attribute, 'name');
                }, ARRAY_FILTER_USE_KEY)
            );
        }

        // 如果以"id"结尾的字段不足10个，则再挑选出integer类型的字段
        if (count($arr) < 10) {
            $arr = array_merge(
                $arr,
                array_filter($rules, function ($rule, $attribute) use ($arr) {
                    return in_array('integer', $rule) and !in_array($attribute, $arr);
                }, ARRAY_FILTER_USE_BOTH)
            );
        }

        $arr = array_slice($arr, 0, 10);

        $str = $this->rulesArrayToString($arr, "\t");
        $stub = str_replace('{{ rules }}', $str, $stub);

        return $this;
    }

    /**
     * 替换模板中的{{ storeRules }}标签
     * @param $stub
     * @return $this
     */
    protected function replaceStoreRules(&$stub)
    {
        $model = $this->newModelInstance();
        $rules = (array)$model->fillableAttributesRules();
        $rules = Tools::removeNullableForRules($rules);

        // 首先过滤出model 自动管理的事件字段
        $arr = array_filter($rules, function ($attribute) {
            if (Model::CREATED_AT === $attribute or Model::UPDATED_AT == $attribute) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);

        $str = $this->rulesArrayToString($arr, "\t", false);
        $stub = str_replace('{{ storeRules }}', $str, $stub);

        return $this;
    }

    /**
     * 替换模板中的{{ updateRules }}标签
     * @param $stub
     * @return $this
     */
    protected function replaceUpdateRules(&$stub)
    {
        $model = $this->newModelInstance();
        $rules = (array)Tools::removeRequiredForRules(
            $model->fillableAttributesRules()
        );
        $rules = Tools::removeNullableForRules($rules);

        // 首先过滤出model 自动管理的事件字段
        $arr = array_filter($rules, function ($attribute) {
            if (Model::CREATED_AT === $attribute or Model::UPDATED_AT == $attribute) {
                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);

        $str = $this->rulesArrayToString($arr, "\t", false);
        $stub = str_replace('{{ updateRules }}', $str, $stub);

        return $this;
    }

    /**
     * 替换模板中的{{ sorts }}标签
     * @param $stub
     * @return $this
     */
    protected function replaceSorts(&$stub)
    {
        $model = $this->newModelInstance();
        $primaryKey = $model->getKeyName();
        if (is_string($primaryKey)) {
            $stub = str_replace('{{ sorts }}', '\'-' . $primaryKey . '\'', $stub);
        }

        return $this;
    }

    /**
     * 将rules规则转化成可输出的字符串
     * @param $rules
     * @param string $prefix
     * @param bool $nullable
     * @return string
     */
    protected function rulesArrayToString($rules, $prefix = '', $nullable = true)
    {
        $str = "[\n";
        foreach ($rules as $name => $val) {
            if (is_string($val)) {
                $val = explode('|', $val);
            }
            if ($nullable) {
                $val = array_merge(['nullable'], $val);
            }

            $str .= $prefix . "\t\t'" . $name . "' => [";
            foreach ($val as $k => $v) {
                $str .= "'" . $v . "', ";
            }
            $str = rtrim($str, ', ');
            $str .= "],\n";
        }
        $str .= $prefix . "\t]\n";

        return rtrim($str, ",\n");
    }

    /**
     * 判断要生成的request form的类名是否与model的类名相同
     * @param $modelClassName
     * @return bool
     */
    protected function isSameWithModel($modelClassName)
    {
        $formClass = str_replace($this->getNamespace($this->className) . '\\', '', $this->className);
        if ($modelClassName == $formClass) {
            return true;
        }
    }

    /**
     * 替换掉模板里面的"{{ class }}"标签
     *
     * @param string $stub
     * @return string
     */
    protected function replaceClass($stub)
    {
        $class = str_replace($this->getNamespace($this->className) . '\\', '', $this->className);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    /**
     * 获取模板文件地址
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->stub) {
            return $this->stub;
        }

        return $this->resolveStubPath('/stubs/form.request.stub');
    }

    /**
     * @param $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = app()->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../..' . $stub;
    }
}
