<?php

namespace Smart\Gii\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * 生成一个Sdk包主要文件
 */
class SdkCreatorService extends BaseService
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
     * 要创建的文件名称
     * @var string
     */
    protected $filename;

    /**
     * @var string
     */
    protected $namespace;

    protected $package;

    /**
     * SdkCreatorService constructor.
     */
    public function __construct()
    {
        $this->files = new Filesystem();
    }

    public static function classes($package, callable $callback = null)
    {
        $package = rtrim($package, '\\');
        $classes = static::stubs($package);
        $response = [];
        if ($callback) {
            foreach ($classes as $class => $stub) {
                $response[] = $callback($class, $stub);
            }
        }

        return $response;
    }

    public static function stubs($package = '')
    {
        return [
            $package . '\Contracts\ApiInterface' => '/stubs/sdk/contracts/ApiInterface.stub',
            $package . '\Contracts\RequestInterface' => '/stubs/sdk/contracts/RequestInterface.stub',
            $package . '\Contracts\ResponseInterface' => '/stubs/sdk/contracts/ResponseInterface.stub',
            $package . '\Client' => '/stubs/sdk/client.stub',
            $package . '\Api' => '/stubs/sdk/api.stub',
        ];
    }

    /**
     * 设置软件包
     * @param $package
     * @return $this
     */
    public function setPackage($package)
    {
        $this->package = trim($package, '\\');

        return $this;
    }

    /**
     * 设置要创建的class
     * @param string $className
     * @return $this
     */
    public function setClassName(string $className)
    {
        $this->className = trim($className, '\\');

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
        $stub = $this->files->get($this->getStub($this->filename));

        return $this->replaceNamespace($stub, $name)
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

    /**
     * @return string
     */
    protected function getStub()
    {
        // print_r(static::stubs($this->package));
        $stub = static::stubs($this->package)[$this->className];

        return $this->resolveStubPath($stub);
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
