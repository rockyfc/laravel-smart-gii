<?php

namespace Smart\Gii\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Smart\Gii\Services\ResourceCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * 创建一个资源类
 * @author Rocky<softfc@163.com>
 */
class ResourceCreator extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'smart:make-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个接口Resource类';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';

    /**
     * @throws FileNotFoundException
     * @return null|bool|void
     */
    public function handle()
    {
        parent::handle();
    }

    /**
     * @param string $name
     * @throws \Exception
     * @throws FileNotFoundException
     * @return string
     * @author Rocky<softfc@163.com>
     */
    protected function buildClass($name)
    {
        return (new ResourceCreatorService(
            $this->argument('name'),
            $this->argument('model')
        ))->getFileContent();
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
            ['name', InputArgument::REQUIRED, 'Resource类名称'],
            ['model', InputArgument::REQUIRED, 'Model类'],
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
