<?php

namespace Smart\Gii\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Smart\Gii\Services\RepositoryCreatorService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * 创建一个Repository类
 * @author Rocky<softfc@163.com>
 */
class RepositoryCreator extends BaseCreator
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'smart:make-repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个Repository类。';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

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
     * @throws FileNotFoundException
     * @return string
     * @author Rocky<softfc@163.com>
     * @date 2020-07-14 16:01
     */
    protected function buildClass($name)
    {
        return (new RepositoryCreatorService(
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
            ['name', InputArgument::REQUIRED, 'Repository类'],
            ['model', InputArgument::OPTIONAL, '表单对应的model类'],
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
