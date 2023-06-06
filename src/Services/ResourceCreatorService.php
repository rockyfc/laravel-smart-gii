<?php

namespace Smart\Gii\Services;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Smart\Common\Sources\Attributes;
use Smart\Common\Sources\SmartModel;

/**
 * 生成一个Resource类文件内容
 */
class ResourceCreatorService extends BaseService
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
     * 设置model类别名
     * 程序有可能会给model类设置别名
     * @var string
     */
    protected $modelClassAlias;

    /**
     * @var string
     */
    protected $stub;

    /**
     * ResourceCreatorService constructor.
     * @param string $className
     * @param string $modelClass
     */
    public function __construct(string $className, string $modelClass)
    {
        $this->files = new Filesystem();
        $this->className = $className;
        $this->modelClass = $modelClass;
        $this->modelClassAlias = $modelClass;
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
     * @throws FileNotFoundException
     * @return mixed
     */
    public function getFileContent()
    {
        return $this->sortImports(
            $this->buildClass($this->className)
        );
    }

    /**
     * @param string $name
     * @throws FileNotFoundException
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModelClass($stub)
            ->replaceDate($stub)
            ->replaceAuthor($stub)
            ->replaceProperties($stub)
            ->replaceAttributes($stub)
            ->replaceRelations($stub)
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
     * 替换掉模板里面的"{{ modelClassName }}" 和"{{ modelClass }}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceModelClass(&$stub)
    {
        $modelClass = $this->modelClass;
        $modelClassName = str_replace($this->getNamespace($modelClass) . '\\', '', $modelClass);

        // 如果model名和request form的类名相同的话，稍做改变
        if ($this->isSameWithModel($modelClassName)) {
            $modelClassName = $modelClassName . 'Model';
            $modelClass = $modelClass . ' as ' . $modelClassName;
        }

        $stub = str_replace(['{{ modelClass }}', '{{modelClass}}'], $modelClass, $stub);
        $stub = str_replace(['{{ modelClassName }}', '{{modelClassName}}'], $modelClassName, $stub);

        return $this;
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
     * 获取model实例
     * @return Attributes|SmartModel
     */
    protected function getModelInstance()
    {
        return new $this->modelClass();
    }

    /**
     * 替换掉模板里面的"{{ date }}" 和"{{date}}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceDate(&$stub)
    {
        $stub = str_replace(['{{ date }}', '{{date}}'], date('Y-m-d'), $stub);

        return $this;
    }

    /**
     * 替换掉模板里面的"{{ author }}" 和"{{author}}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceAuthor(&$stub)
    {
        $stub = str_replace(['{{ author }}', '{{author}}'], get_current_user(), $stub);

        return $this;
    }

    /**
     * 替换模板文件中的"{{ properties }}"标签为具体内容
     * @param $stub
     * @return $this
     */
    protected function replaceProperties(&$stub)
    {
        $columns = $this->getTableColumns();

        $str = '';
        foreach ($columns as $col) {
            $type = trim($this->formatToPhpType($col->getType()), '\\');
            $str .= " * @property {$type} $" . $col->getName() . " {$col->getComment()}\n";
        }
        $str .= ' *';
        $stub = str_replace(['{{ properties }}', '{{properties}}'], $str, $stub);

        return $this;
    }

    /**
     * 替换模板文件中的"{{ attributes }}"标签为具体内容
     * @param $stub
     * @return $this
     */
    protected function replaceAttributes(&$stub)
    {
        $columns = $this->getTableColumns();

        $str = '';
        foreach ($columns as $col) {
            // $type = trim($col->getType(), '\\');
            $str .= "            '" . $col->getName() . "' => " . '$this->' . $col->getName() . ",\n";
            // $str .= " * @property {$type} $" . $col->getName() . " {$col->getComment()}\n";
        }

        $stub = str_replace(['{{ attributes }}', '{{attributes}}'], $str, $stub);

        return $this;
    }

    /**
     * 替换模板文件中的"{{ relations }}"标签为具体内容
     * @param $stub
     * @return $this
     */
    protected function replaceRelations(&$stub)
    {
        $str = '';
        $stub = str_replace(['{{ relations }}', '{{relations}}'], $str, $stub);

        return $this;
    }

    /**
     * 获取数据表的Schema信息
     * @return Column[]
     */
    protected function getTableColumns()
    {
        return $this->getModelInstance()->newSmartSource()->output();
    }

    /**
     * 替换掉模板里面的"{{ class }}"标签
     *
     * @param string $stub
     * @param string $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    /**
     * 获取模板文件地址
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/api.resource.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param string $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = app()->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . '/../..' . $stub;
    }
}
