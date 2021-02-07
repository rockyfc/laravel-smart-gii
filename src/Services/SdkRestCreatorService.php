<?php

namespace Smart\Gii\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Route;

/**
 * 生成一个Sdk Rest类文件内容
 */
class SdkRestCreatorService extends BaseService
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
     * @var string
     */
    protected $namespace;

    /**
     * @var Route
     */
    protected $route;

    /**
     * SdkRestCreatorService constructor.
     */
    public function __construct()
    {
        $this->files = new Filesystem();
    }

    /**
     * @param Route $route
     * @return $this
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * 设置要创建的class
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className)
    {
        $this->className = str_ireplace('\\\\', '\\', $className);

        return $this;
    }

    public function getClassName()
    {
        return $this->className;
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
            ->replaceUri($stub)
            ->replaceMethodArray($stub)
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

    protected function replaceUri(&$stub)
    {
        $stub = str_replace(
            ['{{ uri }}', '{{uri}}'],
            $this->route->uri,
            $stub
        );

        return $this;
    }

    protected function replaceMethodArray(&$stub)
    {
        $replace = "['" . implode("', '", $this->route->methods()) . "']";
        $stub = str_replace(
            ['{{ method_array }}', '{{method_array}}'],
            $replace,
            $stub
        );

        return $this;
    }

    /**
     * @return string
     */
    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/sdk/rest/rest.stub');
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
