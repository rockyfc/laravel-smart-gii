<?php

namespace Smart\Gii\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * 生成一个Controller类文件内容
 */
class ControllerCreatorService extends BaseService
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
     * 基类
     * @var string
     */
    protected $baseClass;

    /**
     * 表单类
     * @var string
     */
    protected $formClass;

    /**
     * 资源类
     * @var string
     */
    protected $resourceClass;

    /**
     * model类
     * @var string
     */
    protected $modelClass;

    /**
     * repository类
     * @var string
     */
    protected $repositoryClass;

    /**
     * @var string
     */
    protected $stub;

    /**
     * 本控制器的建议路由名称
     * @var
     */
    protected $routeName;

    /**
     * RequestFormCreator constructor.
     */
    public function __construct()
    {
        $this->files = new Filesystem();
    }

    /**
     * 设置要创建的class
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * 设置基类
     * @param $baseClass
     * @return $this
     */
    public function setBaseClass($baseClass)
    {
        $this->baseClass = $baseClass;

        return $this;
    }

    /**
     * 设置表单类
     * @param $formClass
     * @return $this
     */
    public function setFormClass($formClass)
    {
        $this->formClass = $formClass;

        return $this;
    }

    /**
     * 设置资源类
     * @param $resourceClass
     * @return $this
     */
    public function setResourceClass($resourceClass)
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    /**
     * 设置modelClass
     * @param string $modelClass
     * @return $this
     */
    public function setModelClass(string $modelClass)
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * 设置repository
     * @param string $repositoryClass
     * @return $this
     */
    public function setRepositoryClass(string $repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;

        return $this;
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
     * 获取路由名
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param string $name
     * @throws FileNotFoundException
     * @return string
     * @author Rocky<softfc@163.com>
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceRequestClass($stub)
            ->replaceModelClass($stub)
            ->replaceResourceClass($stub)
            ->replaceRepositoryClass($stub)
            ->replaceDate($stub)
            ->replaceAuthor($stub)
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
     * 替换掉模板里面的"{{ modelClassName }}" 和"{{ modelClass }}" "{{ model }}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceModelClass(&$stub)
    {
        $modelClass = $this->modelClass;
        $modelClassName = $this->getClassName($modelClass);

        if ($this->modelIsSameWithForm()) {
            $modelClassName = $modelClassName . 'Model';
            $modelClass .= ' as ' . $modelClassName;
        }

        $this->routeName = Str::camel($modelClassName);

        $stub = str_replace(['{{ model }}', '{{model}}'], Str::camel($modelClassName), $stub);
        $stub = str_replace(['{{ modelClass }}', '{{modelClass}}'], $modelClass, $stub);
        $stub = str_replace(['{{ modelClassName }}', '{{modelClassName}}'], $modelClassName, $stub);

        return $this;
    }

    /**
     * 判断model类是不是和form类同名
     * @return bool
     */
    protected function modelIsSameWithForm()
    {
        return $this->getClassName($this->modelClass) == $this->getClassName($this->formClass);
    }

    /**
     * 判断model类是不是和Resource类同名
     * @return bool
     */
    protected function modelIsSameWithResource()
    {
        return $this->getClassName($this->modelClass) == $this->getClassName($this->resourceClass);
    }

    /**
     * 替换掉模板里面的"{{ requestClassName }}" 和"{{ requestClass }}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceRequestClass(&$stub)
    {
        $request = $this->formClass;
        $stub = str_replace(['{{ requestClass }}', '{{requestClass}}'], $request, $stub);

        $className = $this->getClassName($request);
        $stub = str_replace(['{{ requestClassName }}', '{{requestClassName}}'], $className, $stub);

        return $this;
    }

    /**
     * 获取一个去掉命名空间的类名
     * @param $class
     * @return string|string[]
     */
    protected function getClassName($class)
    {
        return str_replace($this->getNamespace($class) . '\\', '', $class);
    }

    /**
     * 替换掉模板里面的"{{ resourceClassName }}" 和"{{ resourceClass }}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceResourceClass(&$stub)
    {
        $resourceClass = $this->resourceClass;
        $resourceClassName = $this->getClassName($resourceClass);
        if ($this->modelIsSameWithResource()) {
            $resourceClassName = $resourceClassName . 'Resource';
            $resourceClass .= ' as ' . $resourceClassName;
        }

        $stub = str_replace(['{{ resourceClass }}', '{{resourceClass}}'], $resourceClass, $stub);
        $stub = str_replace(['{{ resourceClassName }}', '{{resourceClassName}}'], $resourceClassName, $stub);

        return $this;
    }

    /**
     * 替换掉模板里面的"{{ repositoryClass }}" 和"{{ repositoryClassName }}"标签
     * @param $stub
     * @return $this
     */
    protected function replaceRepositoryClass(&$stub)
    {
        $repositoryClass = $this->repositoryClass;
        $repositoryClassName = $this->getClassName($repositoryClass);
        /*if($this->modelIsSameWithResource()){
            $repositoryClassName = $repositoryClassName.'Resource';
            $repositoryClass.=' as '.$repositoryClassName;
        }*/

        $stub = str_replace(['{{ repositoryClass }}', '{{repositoryClass}}'], $repositoryClass, $stub);
        $stub = str_replace(['{{ repositoryClassName }}', '{{repositoryClassName}}'], $repositoryClassName, $stub);

        return $this;
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
        return $this->resolveStubPath('/stubs/api.controller.stub');
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
