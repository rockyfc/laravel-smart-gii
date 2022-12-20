<?php

namespace Smart\Gii\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Routing\Route;
use Smart\Common\Services\DocService;
use Smart\Gii\Services\SdkRestCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class SdkRestCreator
 * @author Rocky<softfc@163.com>
 */
class SdkRestCreator extends BaseCreator
{
    /**
     * @var string
     */
    protected $name = 'smart:make-sdk-rest';

    /**
     * @var string
     */
    protected $description = '创建一个sdk rest接口';

    /**
     * @var string
     */
    protected $type = 'SDK';

    /**
     * @throws FileNotFoundException
     * @return null|bool
     */
    public function handle()
    {
        return parent::handle();
    }

    /**
     * @param string $name
     * @throws \Exception
     * @return string
     */
    protected function buildClass($name)
    {
        return (new SdkRestCreatorService())
            ->setClassName($this->argument('name'))
            ->setSdkNamespace($this->argument('sdk_namespace'))
            ->setRoute($this->getRoute())
            ->getFileContent();
    }

    /**
     * @return Route
     */
    protected function getRoute()
    {
        $service = new DocService();
        $routes = $service->validRoutes();
        foreach ($routes as $route) {
            if ($route->getActionName() == $this->argument('action')) {
                return $route;
            }
        }
    }

    /**
     * 获取模板文件地址
     *
     * @return string
     */
    protected function getStub()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getPath($name)
    {
        if ($path = $this->option('path')) {
            return $path . '/' . str_replace('\\', '/', $name) . '.php';
        }

        return parent::getPath($name);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Sdk相关类名称'],
            ['action', InputArgument::REQUIRED, '路由中的action项'],
            ['sdk_namespace', InputArgument::REQUIRED, 'Sdk包命名空间'],
        ];
    }

    /**
     * @return array|array[]
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, '是否强制执行'],
            ['path', null, InputOption::VALUE_NONE, '类的存放路径'],
        ];
    }
}
