<?php

namespace Smart\Gii\Console;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Smart\Gii\Services\ControllerCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * 创建一个controller类
 *
 * @author Rocky<softfc@163.com>
 */
class ControllerCreator extends BaseCreator
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'smart:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个接口Controller类';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * @throws FileNotFoundException
     * @return bool|void|null
     */
    public function handle()
    {
        parent::handle();
    }

    /**
     * @param string $name
     * @throws FileNotFoundException
     * @return string
     * @author Rocky<softfc@163.com>
     */
    protected function buildClass($name)
    {
        return (new ControllerCreatorService())
            ->setClassName($this->argument('name'))
            ->setBaseClass($this->argument('baseClass'))
            ->setFormClass($this->argument('form'))
            ->setRepositoryClass($this->argument('repository'))
            ->setResourceClass($this->argument('resource'))
            ->setModelClass($this->argument('model'))
            ->getFileContent();
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'Controller类'],
            ['form', InputArgument::REQUIRED, 'Request Form类'],
            ['resource', InputArgument::REQUIRED, 'Resource类'],
            ['repository', InputArgument::REQUIRED, 'Repository类'],
            ['model', InputArgument::REQUIRED, 'Model类'],
            ['baseClass', InputArgument::OPTIONAL, 'Base Controller类', 'Illuminate\Routing\Controller'],
        ];
    }

    /**
     * @return array|array[]
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, '是否强制执行'],
        ];
    }
}
