<?php

namespace Smart\Gii\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Smart\Common\Helpers\Tools;
use Smart\Common\Sources\SmartModel;
use Smart\Gii\Helpers\Dump;

/**
 * 生成一个Repository类
 * @author Rocky<softfc@163.com>
 */
class RepositoryCreatorService extends BaseService
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
     * @param string $className
     * @param $modelClass
     */
    public function __construct(string $className, $modelClass)
    {
        $this->files = new Filesystem();
        $this->className = $className;
        $this->modelClass = $modelClass;
    }

    /**
     * @param $stub
     * @return $this
     */
    public function setStub($stub)
    {
        $this->stub = $stub;

        return $this;
    }

    /**
     * 获取要创建的文件内容
     *
     * @return mixed
     * @throws FileNotFoundException
     */
    public function getFileContent()
    {
        return $this->sortImports(
            $this->buildClass($this->className)
        );
    }

    /**
     * @param $name
     * @return mixed
     * @throws FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModelClass($stub)
            ->replaceFilters($stub)
            ->replaceWhere($stub)
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
     * @param $stub
     * @return $this
     */
    protected function replaceFilters(&$stub)
    {
        $rules = $this->filterAttributesRules();

        $columns = [];
        foreach ($rules as $attribute => $rule) {
            $columns[$attribute] = null;
        }

        $filters = Dump::keyAndValueArray($columns, '    ');

        $stub = str_replace(['{{ filters }}', '{{filters}}'], $filters, $stub);

        return $this;
    }

    /**
     * 替换掉模板里面的"{{ modelClassName }}" 和"{{ modelClass }}"标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceModelClass(&$stub)
    {
        $modelClass = $this->modelClass;
        $modelClassName = str_replace($this->getNamespace($this->modelClass) . '\\', '', $this->modelClass);

        //如果model名和request form的类名相同的话，稍做改变
        if ($this->isSameWithModel($modelClassName)) {
            $modelClassName = $modelClassName . 'Model';
            $modelClass = $modelClass . ' as ' . $modelClassName;
        }

        $stub = str_replace(['{{ modelClassName }}', '{{modelClassName}}'], $modelClassName, $stub);
        $stub = str_replace(['{{ modelClass }}', '{{modelClass}}'], $modelClass, $stub);

        return $this;
    }

    /**
     * 替换模板中的{{ where }}标签
     *
     * @param $stub
     * @return $this
     */
    protected function replaceWhere(&$stub)
    {
        $rules = $this->filterAttributesRules();

        $str = '';
        foreach ($rules as $attribute => $rule) {
            $key = Str::camel($attribute);
            $str .= "\n            ->when(data_get(\$filters, '{$attribute}'), function (\$query, \${$key}) {\n";
            $str .= "                \$query->where('{$attribute}', \${$key});\n";
            $str .= '            })';
        }

        $stub = str_replace(['{{ where }}', '{{where}}'], $str, $stub);

        return $this;
    }

    /**
     * @return Attributes|SmartModel
     */
    protected function newModelInstance()
    {
        return new $this->modelClass();
    }

    /**
     * 获取where筛选条件
     *
     * @return array
     */
    protected function filterAttributesRules()
    {
        $model = $this->newModelInstance();
        $rules = (array)Tools::removeRequiredForRules(
            $model->attributesRules()
        );

        //首先过滤出以"id"结尾的字段
        $arr = array_filter($rules, function ($attribute) {
            return Str::endsWith($attribute, 'id');
        }, ARRAY_FILTER_USE_KEY);

        return array_merge(
            $arr,
            array_filter($rules, function ($rule, $attribute) use ($arr) {
                return in_array('integer', $rule)
                    and !in_array($attribute, $arr)
                    and !in_array($attribute, [$this->modelClass::UPDATED_AT, $this->modelClass::CREATED_AT]);
            }, ARRAY_FILTER_USE_BOTH)
        );
    }

    /**
     * 将rules规则转化成可输出的字符串
     *
     * @param $rules
     * @param $prefix
     * @return string
     * @deprecated
     */
    protected function rulesArrayToString($rules, $prefix = '')
    {
        $str = "[\n";
        foreach ($rules as $name => $val) {
            if (is_string($val)) {
                $val = explode('|', $val);
            }

            $str .= $prefix . "        '" . $name . "' => [";
            foreach ($val as $k => $v) {
                $str .= "'" . $v . "', ";
            }
            $str = rtrim($str, ', ');
            $str .= "],\n";
        }
        $str .= $prefix . "    ]\n";

        return rtrim($str, ",\n");
    }

    /**
     * 判断要生成的request form的类名是否与model的类名相同
     *
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

        return $this->resolveStubPath('/stubs/api.repository.stub');
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
